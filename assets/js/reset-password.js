/**
 * @file reset-password.js
 * @brief Script de gestion du formulaire de réinitialisation de mot de passe.
 * Gère la validation en temps réel, l'affichage de la force du mot de passe
 * et l'envoi asynchrone du formulaire.
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resetPasswordForm');
    const passwordInput = document.getElementById('password');
    const passwordRepeatInput = document.getElementById('password_repeat');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const errorAlert = document.getElementById('errorAlert');
    const successAlert = document.getElementById('successAlert');
    const strengthBar = document.getElementById('strengthBar');
    const matchError = document.getElementById('matchError');
    const loginLink = document.getElementById('loginLink');
    const iconContainer = document.getElementById('iconContainer');
    const pageTitle = document.getElementById('pageTitle');
    const pageSubtitle = document.getElementById('pageSubtitle');

    // Éléments des exigences de mot de passe
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');

    // Boutons de toggle pour afficher/masquer les mots de passe
    const toggleBtns = document.querySelectorAll('.input-group .btn-outline-secondary');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });
    });

    /**
     * Vérifie si le mot de passe respecte toutes les exigences.
     * @param {string} password - Le mot de passe à vérifier.
     * @returns {Object} Objet contenant l'état de chaque exigence.
     */
    function checkPasswordRequirements(password) {
        return {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{}|;:'",.<>?/~`]/.test(password)
        };
    }

    /**
     * Calcule la force du mot de passe.
     * @param {Object} requirements - L'objet des exigences.
     * @returns {Object} Objet avec classe et pourcentage.
     */
    function getPasswordStrength(requirements) {
        const count = Object.values(requirements).filter(Boolean).length;
        if (count <= 1) {
            return { class: 'bg-danger', width: '33%' };
        }
        if (count <= 3) {
            return { class: 'bg-warning', width: '66%' };
        }
        return { class: 'bg-success', width: '100%' };
    }

    /**
     * Met à jour l'affichage des exigences et de la force du mot de passe.
     */
    function updatePasswordRequirements() {
        const password = passwordInput.value;
        const requirements = checkPasswordRequirements(password);

        // Mettre à jour les classes des exigences
        reqLength.classList.toggle('text-success', requirements.length);
        reqUppercase.classList.toggle('text-success', requirements.uppercase);
        reqNumber.classList.toggle('text-success', requirements.number);
        reqSpecial.classList.toggle('text-success', requirements.special);

        // Mettre à jour la barre de force
        const strength = getPasswordStrength(requirements);
        strengthBar.className = 'progress-bar ' + strength.class;
        strengthBar.style.width = strength.width;

        // Mettre à jour la classe de validité de l'input
        if (password.length > 0) {
            const allValid = Object.values(requirements).every(Boolean);
            passwordInput.classList.toggle('is-valid', allValid);
            passwordInput.classList.toggle('is-invalid', !allValid);
        } else {
            passwordInput.classList.remove('is-valid', 'is-invalid');
        }

        // Vérifier la correspondance si le champ de confirmation a une valeur
        if (passwordRepeatInput.value) {
            checkPasswordMatch();
        }
    }

    /**
     * Vérifie si les deux mots de passe correspondent.
     */
    function checkPasswordMatch() {
        const match = passwordInput.value === passwordRepeatInput.value;
        matchError.classList.toggle('d-none', match || !passwordRepeatInput.value);

        if (passwordRepeatInput.value) {
            passwordRepeatInput.classList.toggle('is-valid', match);
            passwordRepeatInput.classList.toggle('is-invalid', !match);
        }
    }

    // Événements de validation en temps réel
    passwordInput.addEventListener('input', updatePasswordRequirements);
    passwordRepeatInput.addEventListener('input', checkPasswordMatch);

    /**
     * Affiche un message d'erreur.
     * @param {string} message - Le message d'erreur à afficher.
     */
    function showError(message) {
        errorAlert.textContent = message;
        errorAlert.classList.remove('d-none');
        successAlert.classList.add('d-none');
    }

    /**
     * Affiche un message de succès et transforme l'interface.
     * @param {string} message - Le message de succès à afficher.
     */
    function showSuccess(message) {
        successAlert.innerHTML = message;
        successAlert.classList.remove('d-none');
        errorAlert.classList.add('d-none');

        // Cacher le formulaire
        form.classList.add('d-none');

        // Changer l'icône
        iconContainer.classList.remove('bg-primary');
        iconContainer.classList.add('bg-success');
        iconContainer.innerHTML = '<i class="bi bi-check-lg fs-3"></i>';

        // Mettre à jour le titre
        pageTitle.textContent = 'Mot de passe réinitialisé !';

        // Mettre à jour le sous-titre
        pageSubtitle.textContent = 'Votre mot de passe a été modifié avec succès.';

        // Afficher le lien de connexion
        loginLink.classList.remove('d-none');
    }

    /**
     * Active ou désactive l'état de chargement du bouton.
     * @param {boolean} loading - True pour activer le chargement.
     */
    function setLoading(loading) {
        submitBtn.disabled = loading;
        btnText.classList.toggle('d-none', loading);
        btnSpinner.classList.toggle('d-none', !loading);
    }

    /**
     * Gestionnaire de soumission du formulaire.
     */
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Validation côté client
        const requirements = checkPasswordRequirements(passwordInput.value);
        if (!Object.values(requirements).every(Boolean)) {
            showError('Votre mot de passe ne respecte pas toutes les exigences de sécurité.');
            return;
        }

        if (passwordInput.value !== passwordRepeatInput.value) {
            showError('Les mots de passe ne correspondent pas.');
            return;
        }

        // Cacher les alertes précédentes
        errorAlert.classList.add('d-none');
        successAlert.classList.add('d-none');

        setLoading(true);

        const formData = new FormData(form);

        try {
            const response = await fetch('/?controller=utilisateur&method=traiterReinitialisation', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                showSuccess(data.message);
            } else {
                showError(data.message || 'Une erreur est survenue.');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showError('Une erreur de connexion est survenue. Veuillez réessayer.');
        } finally {
            setLoading(false);
        }
    });
});

