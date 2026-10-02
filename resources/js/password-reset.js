const role = document.getElementById('recovery-role');
const studentForm = document.getElementById('student-email-reset');
const adminForm = document.getElementById('admin-email-reset');

if (role && studentForm && adminForm) {
    const updateVisibleForm = () => {
        const studentMode = role.value === 'student';

        studentForm.hidden = !studentMode;
        adminForm.hidden = studentMode;

        for (const input of studentForm.querySelectorAll('input[required]')) {
            input.disabled = !studentMode;
        }

        for (const input of adminForm.querySelectorAll('input[required]')) {
            input.disabled = studentMode;
        }
    };

    role.addEventListener('change', updateVisibleForm);
    updateVisibleForm();
}
