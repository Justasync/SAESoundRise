document.addEventListener("DOMContentLoaded", () => {
  const signinForm = document.getElementById("signinForm");
  const signinModal = document.getElementById("signinModal");

  if (!signinForm || !signinModal) {
    return;
  }

  if (typeof bootstrap === "undefined") {
    console.warn("Bootstrap JS is required for the signin modal.");
    return;
  }

  const bootstrapModal = bootstrap.Modal.getOrCreateInstance(signinModal);
  const errorAlert = signinModal.querySelector("[data-signin-error]");
  const successAlert = signinModal.querySelector("[data-signin-success]");

  const showError = (message) => {
    if (errorAlert) {
      errorAlert.textContent = message;
      errorAlert.classList.remove("d-none");
    }
    if (successAlert) {
      successAlert.classList.add("d-none");
    }
  };

  const showSuccess = (message) => {
    if (successAlert) {
      successAlert.textContent = message;
      successAlert.classList.remove("d-none");
    }
    if (errorAlert) {
      errorAlert.classList.add("d-none");
    }
  };

  const hideMessages = () => {
    if (errorAlert) {
      errorAlert.classList.add("d-none");
    }
    if (successAlert) {
      successAlert.classList.add("d-none");
    }
  };

  // Handle form submission
  signinForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    hideMessages();

    // Get form data
    const formData = new FormData(signinForm);
    const email =
      formData.get("email") || document.getElementById("signinEmail")?.value;
    const password =
      formData.get("password") ||
      document.getElementById("signinPassword")?.value;

    if (!email || !password) {
      showError("Veuillez remplir tous les champs.");
      return;
    }

    const submitButton = signinForm.querySelector('button[type="submit"]');
    const originalButtonText = submitButton?.textContent;
    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = "Connexion...";
    }

    try {
      const response = await fetch("/?controller=utilisateur&method=signin", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          email: email,
          password: password,
        }),
      });

      const data = await response.json();

      if (data.success) {
        showSuccess(data.message || "Connexion réussie!");

        setTimeout(async () => {
          bootstrapModal.hide();
          try {
            const response = await fetch('/?controller=home&method=getHeader');
            const headerData = await response.json();
            const header = document.querySelector('header');
            if (header) {
              header.outerHTML = headerData.header;
              const newDropdown = document.getElementById('userDropdown');
              if (newDropdown) {
                new bootstrap.Dropdown(newDropdown);
              }
            }
          } catch (error) {
            console.error('Error fetching header:', error);
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
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = originalButtonText;
      }
    }
  });

  signinModal.addEventListener("shown.bs.modal", () => {
    hideMessages();
    signinForm.reset();
  });
});