document.addEventListener('DOMContentLoaded', function () {
    
    const formSecurite = document.getElementById('formSecurite');
    const boutonOuvrirModalMdp = document.getElementById('boutonOuvrirModalMdp');
    const boutonFinaliserMdp = document.getElementById('boutonFinaliserMdp');
    const modalMdp = new bootstrap.Modal(document.getElementById('modalChangementMdp'));

    // Gestion de la visibilité des mots de passe
    const iconsToggle = document.querySelectorAll('.toggle-password');
    iconsToggle.forEach(icon => {
        icon.addEventListener('click', function() {
            const inputId = this.getAttribute('data-target');
            const input = document.getElementById(inputId);
            if (input && input.type === 'password') {
                input.type = 'text';
                this.classList.replace('bi-eye-slash', 'bi-eye');
            } else if (input) {
                input.type = 'password';
                this.classList.replace('bi-eye', 'bi-eye-slash');
            }
        });
    });

    // Validation et ouverture du modal
    if (boutonOuvrirModalMdp) {
        boutonOuvrirModalMdp.addEventListener('click', function () {
            const mdp = document.getElementById('nouveau_mdp').value;
            const repeter = document.getElementById('repeter_mdp').value;
            const regex = /^(?=.*[a-zA-Z])(?=.*[0-9!@#$%^&*(),.?":{}|<>]).{10,}$/;

            // Vérification de la validité HTML5 (champs requis, etc.)
            if (!formSecurite.checkValidity()) {
                formSecurite.reportValidity();
                return;
            }

            // Vérification de la force du mot de passe
            if (!regex.test(mdp)) {
                alert("Le nouveau mot de passe ne respecte pas les critères de sécurité.");
                return;
            }

            // Vérification de la correspondance
            if (mdp !== repeter) {
                alert("Les nouveaux mots de passe ne correspondent pas.");
                return;
            }

            // Si tout est OK, on ouvre le modal de confirmation
            modalMdp.show();
        });
    }

    // Envoi final du formulaire
    if (boutonFinaliserMdp) {
        boutonFinaliserMdp.addEventListener('click', function () {
            formSecurite.submit();
        });
    }

    // Gestion de la suppression de compte
    const boutonConfirmerSuppression = document.getElementById('boutonConfirmerSuppression');
    if (boutonConfirmerSuppression) {
        boutonConfirmerSuppression.addEventListener('click', function () {
            window.location.href = "?controller=utilisateur&method=supprimerCompte";
        });
    }
});