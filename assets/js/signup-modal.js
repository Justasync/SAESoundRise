/**
 * @file signup-modal.js
 * @brief Gestionnaire de la modale d'inscription utilisateur multi-étapes
 * 
 * @description Ce fichier gère le processus d'inscription en 3 étapes :
 * - Étape 1 : Choix du type de profil (Artiste ou Auditeur)
 * - Étape 2 : Saisie des informations personnelles avec validation
 * - Étape 3 : Confirmation d'inscription et instruction de vérification email
 * 
 * Il inclut une validation complète côté client avant envoi au serveur.
 */

document.addEventListener("DOMContentLoaded", () => {
  /** @type {HTMLElement|null} Élément de la modale Bootstrap */
  const modalElement = document.getElementById("signupModal");

  if (!modalElement) {
    return;
  }

  if (typeof bootstrap === "undefined") {
    // Bootstrap JS est requis pour le modal d'inscription
    console.warn("Bootstrap JS est requis pour le modal d'inscription.");
    return;
  }

  /** @type {bootstrap.Modal} Instance de la modale Bootstrap */
  const bootstrapModal = bootstrap.Modal.getOrCreateInstance(modalElement);
  
  /** @type {HTMLElement[]} Liste des éléments représentant chaque étape */
  const stepElements = Array.from(modalElement.querySelectorAll("[data-step]"));
  
  /** @type {HTMLElement[]} Liste des indicateurs d'étape (cercles numérotés) */
  const stepIndicators = Array.from(
    modalElement.querySelectorAll("[data-step-indicator]")
  );
  
  /** @type {HTMLElement|null} Élément affichant le sous-titre de l'étape */
  const subtitleElement = modalElement.querySelector("[data-step-subtitle]");
  
  /** @type {HTMLElement|null} Élément d'erreur pour l'étape 1 */
  const errorElement = modalElement.querySelector("[data-step-error]");
  
  /** @type {HTMLElement|null} Élément d'affichage des erreurs générales */
  const statusErrorElement = modalElement.querySelector("[data-signup-error]");
  
  /** @type {HTMLElement|null} Élément d'affichage des succès */
  const statusSuccessElement = modalElement.querySelector(
    "[data-signup-success]"
  );
  
  /** @type {HTMLElement|null} Élément affichant l'email de confirmation */
  const confirmationEmail = modalElement.querySelector(
    "[data-confirmation-email]"
  );

  /** @type {HTMLButtonElement|null} Bouton "Suivant/Valider/Fermer" */
  const nextButton = modalElement.querySelector('[data-action="next"]');
  
  /** @type {HTMLButtonElement|null} Bouton "Retour" */
  const backButton = modalElement.querySelector('[data-action="back"]');

  /** @type {number} Étape actuelle du formulaire (1, 2 ou 3) */
  let currentStep = 1;
  
  /** @type {string|null} Type d'utilisateur sélectionné ('artiste' ou 'auditeur') */
  let selectedType = null;
  
  /** @type {string|null} Dernier email soumis pour l'inscription */
  let lastSubmittedEmail = null;

  /**
   * @brief Sous-titres affichés pour chaque étape du formulaire
   * @constant {Object.<number, string>}
   */
  const subtitles = {
    1: "Choisissez votre profil pour commencer.",
    2: "Renseignez les informations pour finaliser votre inscription.",
    3: "Vérifiez votre boîte mail pour activer votre compte.",
  };

  /**
   * @brief Constantes de validation (doivent correspondre aux limites de la base de données)
   * @constant {number}
   */
  const PASSWORD_MIN_LENGTH = 8;
  
  /** @brief Expression régulière pour valider le format du pseudonyme */
  const PSEUDO_REGEX = /^[a-zA-Z0-9_]+$/;
  
  /** @brief Expression régulière pour valider le format de l'email */
  const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  
  /** @brief Longueur maximale de l'email (VARCHAR(191) dans la BDD) */
  const EMAIL_MAX_LENGTH = 191;
  
  /** @brief Longueur maximale du nom (VARCHAR(255) dans la BDD) */
  const NAME_MAX_LENGTH = 255;
  
  /** @brief Longueur maximale de l'URL du site web (VARCHAR(255) dans la BDD) */
  const WEBSITE_MAX_LENGTH = 255;

  /**
   * @brief Valide la sécurité d'un mot de passe
   * 
   * @details Vérifie que le mot de passe respecte les critères de sécurité :
   * - Longueur minimale de 8 caractères
   * - Au moins une lettre majuscule
   * - Au moins un chiffre
   * - Au moins un caractère spécial
   * 
   * @param {string} password - Le mot de passe à valider
   * @returns {{valid: boolean, errors: string[]}} Résultat de la validation avec liste des erreurs
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
   * @brief Valide le format du pseudonyme
   * 
   * @details Le pseudonyme doit :
   * - Contenir entre 3 et 50 caractères
   * - Ne contenir que des lettres, chiffres et underscores
   * 
   * @param {string} pseudo - Le pseudonyme à valider
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
   * @brief Valide le format et la longueur de l'adresse e-mail
   * 
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
   * @brief Valide le format et la longueur de l'URL du site web
   * 
   * @details Ce champ est optionnel. Si renseigné, l'URL doit être valide
   * et ne pas dépasser la longueur maximale autorisée.
   * 
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
   * @brief Valide si l'utilisateur a au moins 13 ans
   * 
   * @details Conformément aux réglementations sur la protection des mineurs,
   * l'âge minimum requis pour créer un compte est de 13 ans.
   * 
   * @param {string} birthdate - Date de naissance au format AAAA-MM-JJ
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
   * @brief Réinitialise l'affichage des messages d'état (succès/erreur)
   * 
   * @returns {void}
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
   * @brief Affiche un message d'erreur
   * 
   * @param {string} message - Message d'erreur à afficher
   * @returns {void}
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
   * @brief Affiche un message de succès
   * 
   * @param {string} message - Message de succès à afficher
   * @returns {void}
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
   * @brief Met à jour l'affichage et l'état des boutons selon l'étape courante
   * 
   * @details Configure le texte et la visibilité des boutons "Retour" et "Suivant"
   * en fonction de l'étape actuelle du formulaire.
   * 
   * @returns {void}
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
   * @brief Affiche l'étape demandée dans le formulaire d'inscription
   * 
   * @details Met à jour l'interface pour afficher l'étape spécifiée :
   * - Affiche/masque les contenus d'étapes
   * - Met à jour les indicateurs d'étape
   * - Met à jour le sous-titre
   * - Configure les boutons
   * 
   * @param {number} step - Numéro de l'étape à afficher (1, 2 ou 3)
   * @returns {void}
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
   * @brief Récupère le formulaire d'inscription du type d'utilisateur sélectionné
   * 
   * @returns {HTMLFormElement|null} Le formulaire actif correspondant au type sélectionné, ou null
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
   * @brief Affiche le formulaire correspondant au type d'utilisateur sélectionné
   * 
   * @details Masque tous les formulaires sauf celui correspondant au type choisi
   * (artiste ou auditeur).
   * 
   * @returns {void}
   */
  const showFormForSelectedType = () => {
    const forms = modalElement.querySelectorAll(".signup-form");
    forms.forEach((form) => {
      form.classList.toggle("d-none", form.dataset.userType !== selectedType);
    });
  };

  /**
   * @brief Gère l'état de chargement du bouton de soumission
   * 
   * @details Désactive le bouton et affiche "Création..." pendant le traitement,
   * puis restaure l'état initial une fois terminé.
   * 
   * @param {boolean} isLoading - Vrai pour activer l'état de chargement
   * @returns {void}
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
   * @brief Soumet le formulaire d'inscription (étape 2)
   * 
   * @details Effectue les opérations suivantes :
   * 1. Validation complète côté client de tous les champs
   * 2. Envoi des données au serveur via requête AJAX POST
   * 3. Gestion de la réponse (succès : passage à l'étape 3, erreur : affichage du message)
   * 
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

    /** @type {string[]} Tableau pour accumuler toutes les erreurs de validation côté client */
    const validationErrors = [];

    // Validation du nom
    if (!nom || nom.trim().length < 1) {
      validationErrors.push("Le nom est requis.");
    } else if (nom.trim().length > NAME_MAX_LENGTH) {
      validationErrors.push(`Le nom ne doit pas dépasser ${NAME_MAX_LENGTH} caractères.`);
    }

    // Validation du pseudonyme
    const pseudoValidation = validatePseudo(pseudo);
    if (!pseudoValidation.valid) {
      validationErrors.push(pseudoValidation.error);
    }

    // Validation de la description
    if (!description || description.trim().length < 10) {
      validationErrors.push("La description doit contenir au moins 10 caractères.");
    }

    // Validation de l'email
    const emailValidation = validateEmail(email);
    if (!emailValidation.valid) {
      validationErrors.push(emailValidation.error);
    }

    // Validation du site web (facultatif)
    const websiteValidation = validateUrl(website);
    if (!websiteValidation.valid) {
      validationErrors.push(websiteValidation.error);
    }

    // Validation de la date de naissance (au moins 13 ans)
    if (!validateAge(birthdate)) {
      validationErrors.push("Vous devez avoir au moins 13 ans pour créer un compte.");
    }

    // Validation de la force du mot de passe
    const passwordValidation = validatePassword(password);
    if (!passwordValidation.valid) {
      validationErrors.push(...passwordValidation.errors);
    }

    // Vérification de la correspondance des mots de passe
    if (password !== passwordRepeat) {
      validationErrors.push("Les mots de passe ne correspondent pas.");
    }

    // Affichage des erreurs côté client s'il y en a
    if (validationErrors.length > 0) {
      showStatusError(validationErrors.join(" "));
      return;
    }

    // Préparation de la requête pour envoyer les données en POST
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

  // ==========================================
  // ÉCOUTEURS D'ÉVÉNEMENTS
  // ==========================================

  /**
   * @brief Gestion du changement de type d'utilisateur à l'étape 1
   */
  modalElement
    .querySelectorAll('input[name="signupUserType"]')
    .forEach((radio) => {
      radio.addEventListener("change", (event) => {
        selectedType = event.target.value;
        errorElement?.classList.add("d-none");
        resetStatusMessages();
      });
    });

  /**
   * @brief Gestion du bouton "Continuer", "Valider" ou "Fermer"
   * 
   * @details Comportement selon l'étape courante :
   * - Étape 1 : Vérifie la sélection du type et passe à l'étape 2
   * - Étape 2 : Soumet le formulaire d'inscription
   * - Étape 3 : Ferme la modale
   */
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

  /**
   * @brief Gestion du bouton "Retour"
   * 
   * @details Permet de revenir à l'étape précédente du formulaire.
   */
  backButton.addEventListener("click", () => {
    if (currentStep === 2) {
      showStep(1);
      return;
    }

    if (currentStep === 3) {
      showStep(2);
    }
  });

  /**
   * @brief Réinitialisation du formulaire lors de la fermeture de la modale
   * 
   * @details Remet tous les champs et états à leur valeur initiale
   * pour préparer une nouvelle inscription.
   */
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
