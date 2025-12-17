document.addEventListener("DOMContentLoaded", () => {
  const signinForm = document.getElementById("signinForm");
  const signinModal = document.getElementById("signinModal");

  // Vérifier que le formulaire et la modale existent avant d'aller plus loin
  if (!signinForm || !signinModal) {
    return;
  }

  // Vérifier la présence de Bootstrap JS
  if (typeof bootstrap === "undefined") {
    console.warn("Bootstrap JS est requis pour la modale de connexion.");
    return;
  }

  const bootstrapModal = bootstrap.Modal.getOrCreateInstance(signinModal);
  const errorAlert = signinModal.querySelector("[data-signin-error]");
  const successAlert = signinModal.querySelector("[data-signin-success]");

  // Constantes pour la validation
  const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  /**
   * Valide le format de l'adresse email
   * @param {string} email - L'adresse email à valider
   * @returns {{valid: boolean, error: string|null}} - Résultat de la validation et message d'erreur si invalide
   */
  const validateEmail = (email) => {
    if (!email || email.trim() === "") {
      return { valid: false, error: "L'adresse e-mail est requise." };
    }
    if (!EMAIL_REGEX.test(email)) {
      return { valid: false, error: "L'adresse e-mail n'est pas valide." };
    }
    return { valid: true, error: null };
  };

  /**
   * Valide le format du mot de passe lors de la connexion
   * @param {string} password - Le mot de passe à valider
   * @returns {{valid: boolean, error: string|null}} - Résultat de la validation et message d'erreur si invalide
   */
  const validatePassword = (password) => {
    if (!password || password === "") {
      return { valid: false, error: "Le mot de passe est requis." };
    }
    return { valid: true, error: null };
  };

  /**
   * Affiche un message d'erreur dans la modale
   * @param {string} message - Message à afficher
   */
  const showError = (message) => {
    if (errorAlert) {
      errorAlert.textContent = message;
      errorAlert.classList.remove("d-none");
    }
    if (successAlert) {
      successAlert.classList.add("d-none");
    }
  };

  /**
   * Affiche un message de succès dans la modale
   * @param {string} message - Message à afficher
   */
  const showSuccess = (message) => {
    if (successAlert) {
      successAlert.textContent = message;
      successAlert.classList.remove("d-none");
    }
    if (errorAlert) {
      errorAlert.classList.add("d-none");
    }
  };

  /**
   * Cache les messages d'alerte (succès ou erreur)
   */
  const hideMessages = () => {
    if (errorAlert) {
      errorAlert.classList.add("d-none");
    }
    if (successAlert) {
      successAlert.classList.add("d-none");
    }
  };

  // Gestion de la soumission du formulaire de connexion
  signinForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    hideMessages();

    // Vérification du HTML5
    if (!signinForm.reportValidity()) {
      return;
    }

    // Récupération des données du formulaire
    const formData = new FormData(signinForm);
    const email =
      formData.get("email") || document.getElementById("signinEmail")?.value;
    const password =
      formData.get("password") ||
      document.getElementById("signinPassword")?.value;

    // Tableau pour les erreurs de validation côté client
    const validationErrors = [];

    // Validation de l'e-mail
    const emailValidation = validateEmail(email);
    if (!emailValidation.valid) {
      validationErrors.push(emailValidation.error);
    }

    // Validation du mot de passe
    const passwordValidation = validatePassword(password);
    if (!passwordValidation.valid) {
      validationErrors.push(passwordValidation.error);
    }

    // Affiche les erreurs de validation s'il y en a
    if (validationErrors.length > 0) {
      showError(validationErrors.join(" "));
      return;
    }

    // Gestion du bouton de soumission (disabled pendant le chargement)
    const submitButton = signinForm.querySelector('button[type="submit"]');
    const originalButtonText = submitButton?.textContent;
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = "Connexion...";
    }

    try {
      // Envoi de la requête de connexion au serveur
      const response = await fetch("/?controller=utilisateur&method=signin", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          email: email.trim(),
          password: password,
        }),
      });

      const data = await response.json();

      if (data.success) {
        showSuccess(data.message || "Connexion réussie!");

        // Attendre 1 seconde avant de fermer la modale et rediriger/recharger
        setTimeout(() => {
          bootstrapModal.hide();
          // On vérifie s'il y a une URL de redirection à utiliser après connexion
          const redirectUrl = signinModal.getAttribute('data-redirect-url') || null;

          if (redirectUrl && redirectUrl.trim() !== '') {
            // Redirige vers l'URL spécifiée
            window.location.href = redirectUrl;
          } else {
            // Recharge la page si pas de redirection spécifique
            window.location.reload();
          }
        }, 1000);
      } else {
        showError(
          data.message || "Une erreur est survenue lors de la connexion."
        );
      }
    } catch (error) {
      showError(
        "Une erreur est survenue. Veuillez réessayer plus tard. " +
          (error?.message || "")
      );
    } finally {
      // Réactiver le bouton de connexion quoi qu'il arrive
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      }
    }
  });
});
