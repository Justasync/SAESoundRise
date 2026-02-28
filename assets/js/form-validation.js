// Validation universelle des formulaires Paaxio
// Ajoute feedback dynamique et empêche la soumission si erreurs

document.addEventListener('DOMContentLoaded', function () {
    // Sélectionne tous les formulaires avec la classe .needs-validation
    var forms = document.querySelectorAll('form.needs-validation');
    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                // Ajoute la classe Bootstrap pour feedback visuel
                form.classList.add('was-validated');
            }
        }, false);
    });
});
