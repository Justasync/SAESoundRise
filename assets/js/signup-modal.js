document.addEventListener("DOMContentLoaded", () => {
  const modalElement = document.getElementById("signupModal");

  if (!modalElement) {
    return;
  }

  if (typeof bootstrap === "undefined") {
    console.warn("Bootstrap JS is required for the signup modal.");
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

  const subtitles = {
    1: "Choisissez votre profil pour commencer.",
    2: "Renseignez les informations pour finaliser votre inscription.",
    3: "Vérifiez votre boîte mail pour activer votre compte.",
  };

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

  const getActiveForm = () => {
    if (!selectedType) {
      return null;
    }

    return modalElement.querySelector(
      `.signup-form[data-user-type="${selectedType}"]`
    );
  };

  const showFormForSelectedType = () => {
    const forms = modalElement.querySelectorAll(".signup-form");
    forms.forEach((form) => {
      form.classList.toggle("d-none", form.dataset.userType !== selectedType);
    });
  };

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

    if (password !== passwordRepeat) {
      showStatusError("Les mots de passe ne correspondent pas.");
      return;
    }

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
      console.error("Signup error:", error);
      showStatusError("Une erreur est survenue. Veuillez réessayer plus tard.");
    } finally {
      setLoading(false);
    }
  };

  modalElement
    .querySelectorAll('input[name="signupUserType"]')
    .forEach((radio) => {
      radio.addEventListener("change", (event) => {
        selectedType = event.target.value;
        errorElement?.classList.add("d-none");
        resetStatusMessages();
      });
    });

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

  backButton.addEventListener("click", () => {
    if (currentStep === 2) {
      showStep(1);
      return;
    }

    if (currentStep === 3) {
      showStep(2);
    }
  });

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

  modalElement.addEventListener("shown.bs.modal", () => {
    showStep(1);
    modalElement.querySelector('[name="signupUserType"]')?.focus();

    // Change the URL to /?controller=home&method=signup without reloading
    const newUrl = "/?controller=home&method=signup";
    if (window.location.search !== "?controller=home&method=signup") {
      window.history.replaceState({}, "", newUrl);
    }
  });

  // when close the modal change to /?controller=home&method=afficer
  modalElement.addEventListener("hidden.bs.modal", () => {
    const newUrl = "/?controller=home&method=afficher";
    if (window.location.search !== "?controller=home&method=afficher") {
      window.history.replaceState({}, "", newUrl);
    }
  });
});
