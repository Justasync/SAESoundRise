document.addEventListener("DOMContentLoaded", () => {
  const modalElement = document.getElementById("signupModal");

  if (!modalElement) {
    return;
  }

  if (typeof bootstrap === "undefined") {
    // Bootstrap JS est requis pour le modal d'inscription.
    console.warn("Bootstrap JS est requis pour le modal d'inscription.");
    return;
  }

  const bootstrapModal = bootstrap.Modal.getOrCreateInstance(modalElement);
  const stepElements = Array.from(modalElement.querySelectorAll("[data-step]"));
  const stepIndicators = Array.from(
    modalElement.querySelectorAll("[data-step-indicator]")
  );
  const subtitleElement = modalElement.querySelector("[data-step-subtitle]");
  const errorElement = modalElement.querySelector("[data-step-error]");
  const statusErrorElement = modalElement.querySelector("[data-signup-error]");
  const statusSuccessElement = modalElement.querySelector(
    "[data-signup-success]"
  );
  const confirmationEmail = modalElement.querySelector(
    "[data-confirmation-email]"
  );

  const nextButton = modalElement.querySelector('[data-action="next"]');
  const backButton = modalElement.querySelector('[data-action="back"]');

  let currentStep = 1;
  let selectedType = null;
  let lastSubmittedEmail = null;

  // Sous-titres pour chaque étape du formulaire d'inscription
  const subtitles = {
    1: "Choisissez votre profil pour commencer.",
    2: "Renseignez les informations pour finaliser votre inscription.",
    3: "Vérifiez votre boîte mail pour activer votre compte.",
  };

  // Constantes pour validation (doivent correspondre aux limites de la base de données)
  const PASSWORD_MIN_LENGTH = 8;
  const PSEUDO_REGEX = /^[a-zA-Z0-9_]+$/;
  const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  const EMAIL_MAX_LENGTH = 191;   // VARCHAR(191) dans la BDD
  const NAME_MAX_LENGTH = 255;    // VARCHAR(255) dans la BDD
  const WEBSITE_MAX_LENGTH = 255; // VARCHAR(255) dans la BDD

  /**
   * Valide la sécurité d'un mot de passe.
   * @param {string} password - Le mot de passe à valider
   * @returns {{valid: boolean, errors: string[]}} Résultat de la validation
   */
  const validatePassword = (password) => {
    const errors = [];

    if (!password || password.length < PASSWORD_MIN_LENGTH) {
      errors.push(`Le mot de passe doit contenir au moins ${PASSWORD_MIN_LENGTH} caractères.`);
    }

    if (!/[A-Z]/.test(password)) {
      errors.push("Le mot de passe doit contenir au moins une lettre majuscule.");
    }

    if (!/[0-9]/.test(password)) {
      errors.push("Le mot de passe doit contenir au moins un chiffre.");
    }

    if (!/[!@#$%^&*()_+\-=\[\]{}|;:'\",.<>?/~`]/.test(password)) {
      errors.push("Le mot de passe doit contenir au moins un symbole (!@#$%^&*...).");
    }

    return {
      valid: errors.length === 0,
      errors
    };
  };

  /**
   * Valide le format du pseudonyme.
   * @param {string} pseudo - Le pseudo à valider
   * @returns {{valid: boolean, error: string|null}} Résultat de la validation
   */
  const validatePseudo = (pseudo) => {
    if (!pseudo || pseudo.length < 3 || pseudo.length > 50) {
      return { valid: false, error: "Le pseudonyme doit contenir entre 3 et 50 caractères." };
    }
    if (!PSEUDO_REGEX.test(pseudo)) {
      return { valid: false, error: "Le pseudonyme ne doit contenir que des lettres, chiffres et underscores, sans espaces." };
    }
    return { valid: true, error: null };
  };

  /**
   * Valide le format et la longueur de l'adresse e-mail.
   * @param {string} email - L'email à valider
   * @returns {{valid: boolean, error: string|null}} Résultat de la validation
   */
  const validateEmail = (email) => {
    if (!email || email.length === 0) {
      return { valid: false, error: "L'adresse e-mail est requise." };
    }
    if (email.length > EMAIL_MAX_LENGTH) {
      return { valid: false, error: `L'adresse e-mail ne doit pas dépasser ${EMAIL_MAX_LENGTH} caractères.` };
    }
    if (!EMAIL_REGEX.test(email)) {
      return { valid: false, error: "L'adresse e-mail n'est pas valide." };
    }
    return { valid: true, error: null };
  };

  /**
   * Valide le format et la longueur de l'URL.
   * @param {string} url - L'URL à valider
   * @returns {{valid: boolean, error: string|null}} Résultat de la validation
   */
  const validateUrl = (url) => {
    if (!url || url.trim() === "") return { valid: true, error: null }; // Champ optionnel
    if (url.length > WEBSITE_MAX_LENGTH) {
      return { valid: false, error: `L'URL du site web ne doit pas dépasser ${WEBSITE_MAX_LENGTH} caractères.` };
    }
    try {
      new URL(url);
      return { valid: true, error: null };
    } catch {
      return { valid: false, error: "L'URL du site web n'est pas valide." };
    }
  };

  /**
   * Valide si la personne a au moins 13 ans.
   * @param {string} birthdate - Date de naissance (format AAAA-MM-JJ)
   * @returns {boolean} Vrai si l'utilisateur a au moins 13 ans
   */
  const validateAge = (birthdate) => {
    if (!birthdate) return false;
    const birth = new Date(birthdate);
    const today = new Date();
    const minDate = new Date(today.getFullYear() - 13, today.getMonth(), today.getDate());
    return birth <= minDate;
  };

  /**
   * Réinitialise l'affichage des messages d'état (succès/erreur).
   */
  const resetStatusMessages = () => {
    if (statusErrorElement) {
      statusErrorElement.classList.add("d-none");
      statusErrorElement.textContent = "";
    }
    if (statusSuccessElement) {
      statusSuccessElement.classList.add("d-none");
      statusSuccessElement.textContent = "";
    }
  };

  /**
   * Affiche un message d'erreur.
   * @param {string} message - Message à afficher
   */
  const showStatusError = (message) => {
    if (!statusErrorElement) {
      return;
    }
    statusErrorElement.textContent = message;
    statusErrorElement.classList.remove("d-none");
    if (statusSuccessElement) {
      statusSuccessElement.classList.add("d-none");
    }
  };

  /**
   * Affiche un message de succès.
   * @param {string} message - Message de succès à afficher
   */
  const showStatusSuccess = (message) => {
    if (!statusSuccessElement) {
      return;
    }
    statusSuccessElement.textContent = message;
    statusSuccessElement.classList.remove("d-none");
    if (statusErrorElement) {
      statusErrorElement.classList.add("d-none");
    }
  };

  /**
   * Met à jour l'affichage et l'état des boutons selon l'étape affichée.
   */
  const updateButtons = () => {
    if (currentStep === 1) {
      backButton.classList.add("d-none");
      nextButton.textContent = "Continuer";
      nextButton.disabled = false;
    } else if (currentStep === 2) {
      backButton.classList.remove("d-none");
      nextButton.textContent = "Valider";
      nextButton.disabled = false;
    } else {
      backButton.classList.add("d-none");
      nextButton.textContent = "Fermer";
      nextButton.disabled = false;
    }
  };

  /**
   * Affiche l'étape demandée dans le formulaire d'inscription.
   * @param {number} step - Numéro de l'étape à afficher
   */
  const showStep = (step) => {
    currentStep = step;

    stepElements.forEach((stepElement) => {
      stepElement.classList.toggle(
        "d-none",
        Number(stepElement.dataset.step) !== step
      );
    });

    stepIndicators.forEach((indicator) => {
      const indicatorStep = Number(indicator.dataset.stepIndicator);
      indicator.classList.toggle("active", indicatorStep === step);
      indicator.classList.toggle("completed", indicatorStep < step);
    });

    if (step === 1 && errorElement) {
      errorElement.classList.add("d-none");
    }

    if (step !== 2) {
      resetStatusMessages();
    }

    if (subtitleElement) {
      subtitleElement.textContent = subtitles[step];
    }

    updateButtons();
  };

  /**
   * Récupère le formulaire d'inscription du type d'utilisateur sélectionné.
   * @returns {HTMLFormElement|null} Le formulaire actif ou null
   */
  const getActiveForm = () => {
    if (!selectedType) {
      return null;
    }

    return modalElement.querySelector(
      `.signup-form[data-user-type="${selectedType}"]`
    );
  };

  /**
   * Affiche le formulaire correspondant au type d'utilisateur sélectionné.
   */
  const showFormForSelectedType = () => {
    const forms = modalElement.querySelectorAll(".signup-form");
    forms.forEach((form) => {
      form.classList.toggle("d-none", form.dataset.userType !== selectedType);
    });
  };

  /**
   * Affiche ou masque le bouton suivant comme "chargement".
   * @param {boolean} isLoading - Vrai si en chargement
   */
  const setLoading = (isLoading) => {
    if (!nextButton) {
      return;
    }

    if (isLoading) {
      nextButton.disabled = true;
      nextButton.dataset.originalText =
        nextButton.dataset.originalText || nextButton.textContent;
      nextButton.textContent = "Création...";
    } else {
      nextButton.disabled = false;
      if (nextButton.dataset.originalText) {
        nextButton.textContent = nextButton.dataset.originalText;
        delete nextButton.dataset.originalText;
      } else {
        updateButtons();
      }
    }
  };

  /**
   * Soumet le formulaire d'inscription (étape 2), avec validation côté client.
   * Effectue la requête AJAX et affiche les éventuelles erreurs ou le message de confirmation.
   * @returns {Promise<void>}
   */
  const submitSignup = async () => {
    const activeForm = getActiveForm();

    if (!selectedType || !activeForm) {
      showStatusError("Veuillez sélectionner un type de profil.");
      return;
    }

    if (!activeForm.reportValidity()) {
      return;
    }

    resetStatusMessages();

    const formData = new FormData(activeForm);
    const password = formData.get("password");
    const passwordRepeat = formData.get("password_repeat");
    const pseudo = formData.get("pseudo");
    const email = formData.get("email");
    const website = formData.get("website");
    const birthdate = formData.get("birthdate");
    const nom = formData.get("nom");
    const description = formData.get("description");

    // Tableau pour accumuler toutes les erreurs de validation côté client
    const validationErrors = [];

    // Valider le nom
    if (!nom || nom.trim().length < 1) {
      validationErrors.push("Le nom est requis.");
    } else if (nom.trim().length > NAME_MAX_LENGTH) {
      validationErrors.push(`Le nom ne doit pas dépasser ${NAME_MAX_LENGTH} caractères.`);
    }

    // Valider le pseudo
    const pseudoValidation = validatePseudo(pseudo);
    if (!pseudoValidation.valid) {
      validationErrors.push(pseudoValidation.error);
    }

    // Valider la description
    if (!description || description.trim().length < 10) {
      validationErrors.push("La description doit contenir au moins 10 caractères.");
    }

    // Valider l'email
    const emailValidation = validateEmail(email);
    if (!emailValidation.valid) {
      validationErrors.push(emailValidation.error);
    }

    // Valider le site web (facultatif)
    const websiteValidation = validateUrl(website);
    if (!websiteValidation.valid) {
      validationErrors.push(websiteValidation.error);
    }

    // Valider la date de naissance (au moins 13 ans)
    if (!validateAge(birthdate)) {
      validationErrors.push("Vous devez avoir au moins 13 ans pour créer un compte.");
    }

    // Valider la force du mot de passe
    const passwordValidation = validatePassword(password);
    if (!passwordValidation.valid) {
      validationErrors.push(...passwordValidation.errors);
    }

    // Vérifier la correspondance des mots de passe
    if (password !== passwordRepeat) {
      validationErrors.push("Les mots de passe ne correspondent pas.");
    }

    // Afficher les erreurs côté client s'il y en a
    if (validationErrors.length > 0) {
      showStatusError(validationErrors.join(" "));
      return;
    }

    // Préparer la requête pour envoyer les données en POST
    const payload = new URLSearchParams();
    payload.append("type", selectedType);
    formData.forEach((value, key) => {
      if (typeof value === "string") {
        const shouldTrim = key !== "password" && key !== "password_repeat";
        payload.append(key, shouldTrim ? value.trim() : value);
      } else if (value != null) {
        payload.append(key, value);
      }
    });

    setLoading(true);

    try {
      const response = await fetch("/?controller=utilisateur&method=signup", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: payload,
      });

      const data = await response.json();

      if (!response.ok || !data.success) {
        showStatusError(
          data.message ||
            "Impossible de créer le compte. Veuillez vérifier les informations fournies."
        );
        return;
      }

      lastSubmittedEmail =
        data.user?.email || formData.get("email") || "votre adresse.";
      if (confirmationEmail) {
        confirmationEmail.textContent = lastSubmittedEmail;
      }

      showStatusSuccess(data.message || "Compte créé.");
      showStep(3);
    } catch (error) {
      // Affiche une erreur technique lors de la soumission AJAX
      console.error("Erreur lors de l'inscription :", error);
      showStatusError("Une erreur est survenue. Veuillez réessayer plus tard.");
    } finally {
      setLoading(false);
    }
  };

  // Gestion du changement de type d'utilisateur à l'étape 1
  modalElement
    .querySelectorAll('input[name="signupUserType"]')
    .forEach((radio) => {
      radio.addEventListener("change", (event) => {
        selectedType = event.target.value;
        errorElement?.classList.add("d-none");
        resetStatusMessages();
      });
    });

  // Gestion bouton "Continuer", "Valider" ou "Fermer"
  nextButton.addEventListener("click", () => {
    if (currentStep === 1) {
      if (!selectedType) {
        errorElement?.classList.remove("d-none");
        return;
      }
      showFormForSelectedType();
      showStep(2);
      return;
    }

    if (currentStep === 2) {
      submitSignup();
      return;
    }

    if (currentStep === 3) {
      bootstrapModal.hide();
    }
  });

  // Gestion bouton "Retour"
  backButton.addEventListener("click", () => {
    if (currentStep === 2) {
      showStep(1);
      return;
    }

    if (currentStep === 3) {
      showStep(2);
    }
  });

  // Réinitialisation du formulaire lors de la fermeture du modal
  modalElement.addEventListener("hidden.bs.modal", () => {
    const forms = modalElement.querySelectorAll(".signup-form");
    forms.forEach((form) => form.reset());

    modalElement
      .querySelectorAll('input[name="signupUserType"]')
      .forEach((radio) => {
        radio.checked = false;
      });

    selectedType = null;
    lastSubmittedEmail = null;
    errorElement?.classList.add("d-none");
    resetStatusMessages();
    showStep(1);
  });
});
