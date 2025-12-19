/**
 * @file forgot-password.js
 * @brief Script de gestion du formulaire de mot de passe oublié.
 * Gère l'envoi asynchrone du formulaire et l'affichage des messages.
 */
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('forgotPasswordForm');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const errorAlert = document.getElementById('errorAlert');
    const successAlert = document.getElementById('successAlert');
    const iconContainer = document.getElementById('iconContainer');
    const pageTitle = document.getElementById('pageTitle');
    const pageSubtitle = document.getElementById('pageSubtitle');

    /**
     * Affiche un message d'erreur dans l'alerte dédiée.
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
        successAlert.textContent = message;
        successAlert.classList.remove('d-none');
        errorAlert.classList.add('d-none');
        
        // Cacher le formulaire et afficher un état de succès
        form.classList.add('d-none');
        
        // Changer l'icône pour une coche
        iconContainer.classList.remove('bg-primary');
        iconContainer.classList.add('bg-success');
        iconContainer.innerHTML = '<i class="bi bi-check-lg fs-3"></i>';
        
        // Mettre à jour le titre
        pageTitle.textContent = 'Email envoyé !';
        
        // Mettre à jour le sous-titre
        pageSubtitle.innerHTML = message + '<br><br><small class="text-muted">N\'oubliez pas de vérifier vos spams si vous ne trouvez pas l\'email.</small>';
    }

    /**
     * Active ou désactive l'état de chargement du bouton.
     * @param {boolean} loading - True pour activer le chargement, false sinon.
     */
    function setLoading(loading) {
        submitBtn.disabled = loading;
        btnText.classList.toggle('d-none', loading);
        btnSpinner.classList.toggle('d-none', !loading);
    }

    /**
     * Gestionnaire de soumission du formulaire.
     * Envoie la demande de réinitialisation via AJAX.
     */
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Cacher les alertes précédentes
        errorAlert.classList.add('d-none');
        successAlert.classList.add('d-none');
        
        setLoading(true);

        const formData = new FormData(form);

        try {
            const response = await fetch('/?controller=utilisateur&method=demanderReinitialisation', {
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

