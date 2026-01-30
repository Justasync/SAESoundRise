document.addEventListener('DOMContentLoaded', function () {
    const btnValider = document.getElementById('btnValiderPaiement');
    const modalPaiementElement = document.getElementById('modalPaiement');
    const modalFelicitationElement = document.getElementById('modalFelicitation');
    const errorDiv = document.getElementById('payment-errors');

    // === A. FORMATAGE EN TEMPS RÉEL ===

    // Numéro de carte : ajoute des espaces tous les 4 chiffres
    document.getElementById('card_number').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '');
        e.target.value = val.replace(/(.{4})/g, '$1 ').trim();
    });

    // Date d'expiration : ajoute le "/" automatiquement
    document.getElementById('card_expiry').addEventListener('input', function (e) {
        let val = e.target.value.replace(/\D/g, '');
        if (val.length >= 2) {
            e.target.value = val.substring(0, 2) + '/' + val.substring(2, 4);
        } else {
            e.target.value = val;
        }
    });

    // CVV : chiffres uniquement
    document.getElementById('card_cvv').addEventListener('input', function (e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    // === B. ALGORITHME DE LUHN (Validation mathématique) ===
    function isValidLuhn(number) {
        let str = number.replace(/\s/g, '');
        let sum = 0;
        let shouldDouble = false;
        for (let i = str.length - 1; i >= 0; i--) {
            let digit = parseInt(str.charAt(i));
            if (shouldDouble) {
                if ((digit *= 2) > 9) digit -= 9;
            }
            sum += digit;
            shouldDouble = !shouldDouble;
        }
        return (sum % 10) === 0;
    }

    // === C. LOGIQUE DE VALIDATION FINALE ===
    if (btnValider) {
        btnValider.addEventListener('click', function () {
            const num = document.getElementById('card_number').value;
            const exp = document.getElementById('card_expiry').value;
            const cvv = document.getElementById('card_cvv').value;
            const name = document.getElementById('card_name').value;

            errorDiv.style.display = 'none';

            // 1. Vérification champs vides
            if (!num || !exp || !cvv || !name) {
                return showError("Veuillez remplir tous les champs.");
            }

            // 2. Validation Numéro (Luhn)
            if (num.replace(/\s/g, '').length < 16 || !isValidLuhn(num)) {
                return showError("Numéro de carte invalide.");
            }

            // 3. Validation Expiration (Format et Date future)
            const expRegex = /^(0[1-9]|1[0-2])\/([0-9]{2})$/;
            if (!expRegex.test(exp)) {
                return showError("Format date invalide (MM/AA).");
            }
            
            const today = new Date();
            const currentMonth = today.getMonth() + 1;
            const currentYear = parseInt(today.getFullYear().toString().substr(2, 2));
            const [expMonth, expYear] = exp.split('/').map(v => parseInt(v));

            if (expYear < currentYear || (expYear === currentYear && expMonth < currentMonth)) {
                return showError("La carte est expirée.");
            }

            // 4. Validation CVV
            if (cvv.length < 3) {
                return showError("CVV invalide (3 chiffres).");
            }

            // SI TOUT EST VALIDE : Enchaînement des modals
            const bsModalPaiement = bootstrap.Modal.getInstance(modalPaiementElement);
            bsModalPaiement.hide();

            setTimeout(() => {
                const bsModalFelicitation = new bootstrap.Modal(modalFelicitationElement);
                bsModalFelicitation.show();
            }, 400);
        });
    }

    function showError(msg) {
        errorDiv.innerText = msg;
        errorDiv.style.display = 'block';
    }
});