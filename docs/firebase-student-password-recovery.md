# Firebase SMS for student password recovery

Student password recovery uses Firebase Phone Authentication for the SMS code. Admin password recovery continues to use email.

## Firebase project setup

1. In Firebase Console, open the project and enable **Authentication → Sign-in method → Phone**.
2. Add the local and production hostnames under **Authentication → Settings → Authorized domains**.
3. Register a Firebase Web app and copy its `apiKey`, `authDomain`, `projectId`, and `appId` into the environment below. These are client configuration values, not service-account credentials.
4. Set `FIREBASE_PROJECT_ID` to the same project ID. Laravel verifies the Firebase ID token against Google's published signing keys; no Firebase service-account private key is needed.
5. Build the frontend after setting the `VITE_FIREBASE_*` values because Vite embeds them into the compiled bundle.

```dotenv
FIREBASE_PROJECT_ID=your-firebase-project-id
VITE_FIREBASE_API_KEY=your-web-api-key
VITE_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=your-firebase-project-id
VITE_FIREBASE_APP_ID=your-web-app-id
```

Student profile phone values may be stored as Malaysian local numbers (`0123456789`) or international numbers (`+60123456789`). During recovery, students enter the phone number saved on their own profile. Laravel checks the matric number and stored phone before Firebase sends an SMS, then checks Firebase's signed token and phone claim before allowing a password reset.

Firebase's web phone flow uses reCAPTCHA and Firebase applies SMS quotas and abuse controls. See the [Firebase phone authentication setup](https://firebase.google.com/docs/auth/web/phone-auth) and [ID token verification guidance](https://firebase.google.com/docs/auth/admin/verify-id-tokens) before enabling this on the production hostname.
