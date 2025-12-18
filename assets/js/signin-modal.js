/**
 * @file signin-modal.js
 * @brief Gestionnaire de la modale de connexion utilisateur
 * 
 * @description Ce fichier gère le formulaire de connexion dans une modale Bootstrap.
 * Il inclut la validation côté client de l'email et du mot de passe,
 * l'envoi des données au serveur via AJAX, et la gestion des messages
 * de succès/erreur.
 */

document.addEventListener("DOMContentLoaded", () => {
  /** @type {HTMLFormElement|null} Formulaire de connexion */
  const signinForm = document.getElementById("signinForm");
  
  /** @type {HTMLElement|null} Élément de la modale Bootstrap */
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

  /** @type {bootstrap.Modal} Instance de la modale Bootstrap */
  const bootstrapModal = bootstrap.Modal.getOrCreateInstance(signinModal);
  
  /** @type {HTMLElement|null} Élément d'affichage des erreurs */
  const errorAlert = signinModal.querySelector("[data-signin-error]");
  
  /** @type {HTMLElement|null} Élément d'affichage des succès */
  const successAlert = signinModal.querySelector("[data-signin-success]");

  /**
   * @brief Expression régulière pour valider le format d'un email
   * @constant {RegExp}
   */
  const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  /**
   * @brief Valide le format de l'adresse email
   * 
   * @param {string} email - L'adresse email à valider
   * @returns {{valid: boolean, error: string|null}} Résultat de la validation et message d'erreur si invalide
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
   * @brief Valide le format du mot de passe lors de la connexion
   * 
   * @param {string} password - Le mot de passe à valider
   * @returns {{valid: boolean, error: string|null}} Résultat de la validation et message d'erreur si invalide
   */
  const validatePassword = (password) => {
    if (!password || password === "") {
      return { valid: false, error: "Le mot de passe est requis." };
    }
    return { valid: true, error: null };
  };

  /**
   * @brief Affiche un message d'erreur dans la modale
   * 
   * @param {string} message - Message d'erreur à afficher
   * @returns {void}
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
   * @brief Affiche un message de succès dans la modale
   * 
   * @param {string} message - Message de succès à afficher
   * @returns {void}
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
   * @brief Cache les messages d'alerte (succès ou erreur)
   * 
   * @returns {void}
   */
  const hideMessages = () => {
    if (errorAlert) {
      errorAlert.classList.add("d-none");
    }
    if (successAlert) {
      successAlert.classList.add("d-none");
    }
  };

  /**
   * @brief Gestionnaire de soumission du formulaire de connexion
   * 
   * @details Valide les champs du formulaire côté client, envoie les données
   * au serveur via une requête POST AJAX, et gère la réponse (succès ou erreur).
   * En cas de succès, ferme la modale et redirige/recharge la page.
   * 
   * @param {Event} e - L'événement de soumission du formulaire
   * @returns {Promise<void>}
   */
  signinForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    hideMessages();

    // Vérification de la validation HTML5 native
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

    /** @type {string[]} Tableau pour accumuler les erreurs de validation côté client */
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

    // Gestion du bouton de soumission (désactivé pendant le chargement)
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
          // Vérification d'une URL de redirection après connexion
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
