/**
 * LoginPresenter — invia email e password al backend e salva il JWT.
 */
(function () {
    const form = document.getElementById('login-form');
    const message = document.getElementById('message');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    if (!form || !message || !emailInput || !passwordInput) {
        return;
    }

    if (ApiClient.getToken()) {
        window.location.replace('profile.html');
        return;
    }

    function showMessage(text, type = 'info') {
        message.textContent = text;
        message.className = `alert alert-${type} mt-3`;
        message.classList.remove('d-none');
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const email = emailInput.value.trim();
        const password = passwordInput.value;

        if (!email || !password) {
            showMessage('Inserisci email e password', 'warning');
            return;
        }

        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.textContent = 'Accedi...';
        showMessage('Invio credenziali...', 'info');

        try {
            const data = await ApiClient.post('/auth/login', { email, password });
            ApiClient.setToken(data.token);
            showMessage('Login effettuato. Reindirizzamento...', 'success');
            window.location.replace('profile.html');
        } catch (error) {
            showMessage(error.message || 'Errore durante il login', 'danger');
        } finally {
            submitButton.disabled = false;
            submitButton.textContent = 'Accedi';
        }
    });
})();
