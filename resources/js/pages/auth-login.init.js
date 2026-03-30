(function () {
    'use strict';

    var form = document.getElementById('login-form');
    if (!form) {
        return;
    }

    var emailInput = document.getElementById('email');
    var passwordInput = document.getElementById('password');
    var mfaInput = document.getElementById('mfa_token');
    var mfaGroup = document.getElementById('mfa-group');
    var rememberInput = document.getElementById('remember-check');
    var submitButton = document.getElementById('login-submit');
    var errorBox = document.getElementById('login-error');

    function setError(el, message) {
        if (!el) {
            return;
        }

        if (message) {
            el.textContent = message;
            el.classList.remove('d-none');
        } else {
            el.textContent = '';
            el.classList.add('d-none');
        }
    }

    function clearFieldError(inputId, errorId) {
        var input = document.getElementById(inputId);
        var error = document.getElementById(errorId);

        if (input) {
            input.classList.remove('is-invalid');
        }

        if (error) {
            error.textContent = '';
        }
    }

    function markFieldError(inputId, errorId, message) {
        var input = document.getElementById(inputId);
        var error = document.getElementById(errorId);

        if (input) {
            input.classList.add('is-invalid');
        }

        if (error) {
            error.textContent = message || '';
        }
    }

    function clearErrors() {
        setError(errorBox, '');
        clearFieldError('email', 'email-error');
        clearFieldError('password', 'password-error');
        clearFieldError('mfa_token', 'mfa-error');
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearErrors();

        var payload = {
            email: emailInput ? emailInput.value.trim() : '',
            password: passwordInput ? passwordInput.value : ''
        };

        var mfaValue = mfaInput ? mfaInput.value.trim() : '';
        if (mfaValue) {
            payload.mfa_token = mfaValue;
        }

        if (submitButton) {
            submitButton.disabled = true;
        }

        try {
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            var response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken ? csrfToken.getAttribute('content') : ''
                },
                body: JSON.stringify(payload)
            });

            var result = {};
            try {
                result = await response.json();
            } catch (e) {
                result = {};
            }

            if (response.ok) {
                var token = result && result.data ? result.data.access_token : null;
                var user = result && result.data ? result.data.user : null;

                if (!token) {
                    setError(errorBox, 'Login failed: token not returned.');
                    return;
                }

                var storage = rememberInput && rememberInput.checked ? window.localStorage : window.sessionStorage;
                storage.setItem('access_token', token);
                if (user) {
                    storage.setItem('auth_user', JSON.stringify(user));
                }

                window.location.href = form.dataset.redirect || '/';
                return;
            }

            var message = result && result.message ? result.message : 'Login failed.';
            var errors = result && result.errors ? result.errors : null;

            if (response.status === 403 && message.indexOf('MFA verification required') !== -1) {
                if (mfaGroup) {
                    mfaGroup.classList.remove('d-none');
                }
                setError(errorBox, 'MFA required. Enter your authenticator code and submit again.');
                return;
            }

            if (response.status === 422 && errors) {
                if (errors.email && errors.email[0]) {
                    markFieldError('email', 'email-error', errors.email[0]);
                }
                if (errors.password && errors.password[0]) {
                    markFieldError('password', 'password-error', errors.password[0]);
                }
                if (errors.mfa_token && errors.mfa_token[0]) {
                    if (mfaGroup) {
                        mfaGroup.classList.remove('d-none');
                    }
                    markFieldError('mfa_token', 'mfa-error', errors.mfa_token[0]);
                }
                setError(errorBox, message);
                return;
            }

            setError(errorBox, message);
        } catch (error) {
            setError(errorBox, 'Unable to reach the server. Please try again.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    });
})();
