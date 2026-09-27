import { initializeApp } from 'firebase/app';
import { getAuth, RecaptchaVerifier, signInWithPhoneNumber, signOut } from 'firebase/auth';

const form = document.getElementById('student-phone-reset');
if (form) {
    const configNode = document.getElementById('firebase-web-config');
    const firebaseConfig = JSON.parse(configNode?.textContent || '{}');
    const role = document.getElementById('recovery-role');
    const studentForm = document.getElementById('student-phone-reset');
    const adminForm = document.getElementById('admin-email-reset');
    const details = document.getElementById('student-reset-details');
    const codeStep = document.getElementById('student-reset-code-step');
    const status = document.getElementById('student-reset-status');
    const destination = document.getElementById('student-code-destination');
    const sendButton = document.getElementById('send-student-code');
    const verifyButton = document.getElementById('verify-student-code');
    const resendButton = document.getElementById('resend-student-code');
    let confirmation = null;
    let challengeId = null;
    let auth = null;
    let recaptcha = null;

    const setRole = () => {
        const studentMode = role.value === 'student';
        studentForm.hidden = !studentMode;
        adminForm.hidden = studentMode;
        [...studentForm.querySelectorAll('input[required]')].forEach((input) => { input.disabled = !studentMode; });
        [...adminForm.querySelectorAll('input[required]')].forEach((input) => { input.disabled = studentMode; });
    };
    role.addEventListener('change', setRole);
    setRole();

    const requestJson = async (url, payload) => {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(payload),
        });
        const body = await response.json().catch(() => ({}));
        if (!response.ok) throw new Error(body.message || 'Unable to verify this request.');
        return body;
    };

    const initializeFirebase = () => {
        if (!firebaseConfig.apiKey || !firebaseConfig.authDomain || !firebaseConfig.projectId || !firebaseConfig.appId) {
            throw new Error('Firebase Phone Authentication is not configured for this site yet.');
        }
        if (!auth) auth = getAuth(initializeApp(firebaseConfig));
        auth.languageCode = document.documentElement.lang?.startsWith('ms') ? 'ms' : 'en';
        if (!recaptcha) recaptcha = new RecaptchaVerifier(auth, 'firebase-recaptcha-container', { size: 'normal' });
        return auth;
    };

    const sendCode = async () => {
        status.textContent = 'Checking account and phone number…';
        sendButton.disabled = true;
        try {
            const data = await requestJson(form.dataset.prepareUrl, {
                identifier: document.getElementById('student-identifier').value.trim(),
                phone: document.getElementById('student-phone').value.trim(),
            });
            challengeId = data.challenge_id;
            const firebaseAuth = initializeFirebase();
            confirmation = await signInWithPhoneNumber(firebaseAuth, data.phone, recaptcha);
            details.hidden = true;
            codeStep.hidden = false;
            destination.textContent = `A verification code was sent to ${data.phone.replace(/\d(?=\d{4})/g, '•')}.`;
            status.textContent = '';
            document.getElementById('student-otp').focus();
        } catch (error) {
            status.textContent = error.message || 'Unable to send the verification code.';
            if (recaptcha) {
                recaptcha.clear();
                recaptcha = null;
            }
            document.getElementById('firebase-recaptcha-container').replaceChildren();
        } finally {
            sendButton.disabled = false;
        }
    };

    sendButton.addEventListener('click', sendCode);
    resendButton.addEventListener('click', () => {
        codeStep.hidden = true;
        details.hidden = false;
        confirmation = null;
        status.textContent = 'Complete the reCAPTCHA again to request a new code.';
        document.getElementById('firebase-recaptcha-container').replaceChildren();
        if (recaptcha) recaptcha.clear();
        recaptcha = null;
    });

    verifyButton.addEventListener('click', async () => {
        const code = document.getElementById('student-otp').value.trim();
        if (!confirmation || !/^\d{6}$/.test(code)) {
            status.textContent = 'Enter the 6-digit code from the SMS.';
            return;
        }
        verifyButton.disabled = true;
        status.textContent = 'Verifying code…';
        try {
            const credential = await confirmation.confirm(code);
            const idToken = await credential.user.getIdToken(true);
            await signOut(auth);
            const result = await requestJson(form.dataset.completeUrl, {
                challenge_id: challengeId,
                identifier: document.getElementById('student-identifier').value.trim(),
                id_token: idToken,
            });
            window.location.assign(result.redirect);
        } catch (error) {
            status.textContent = error.message || 'The verification code could not be confirmed.';
        } finally {
            verifyButton.disabled = false;
        }
    });
}
