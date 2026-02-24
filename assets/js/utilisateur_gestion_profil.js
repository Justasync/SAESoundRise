document.addEventListener('DOMContentLoaded', function () {
    const formulaire = document.getElementById('formulaireProfil');
    const boutonOuvrirModal = document.getElementById('boutonOuvrirModal');
    const boutonConfirmer = document.getElementById('boutonConfirmer');

    const modalElement = document.getElementById('modalConfirmation');
    if (modalElement) {
        const modalConfirmation = new bootstrap.Modal(modalElement);

        if (boutonOuvrirModal) {
            boutonOuvrirModal.addEventListener('click', function () {
                if (formulaire.checkValidity()) {
                    modalConfirmation.show();
                } else {
                    formulaire.reportValidity();
                }
            });
        }
    }

    if (boutonConfirmer && formulaire) {
        boutonConfirmer.addEventListener('click', function () {
            formulaire.submit();
        });
    }
});