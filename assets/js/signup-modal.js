document.addEventListener('DOMContentLoaded', () => {
	const modalElement = document.getElementById('signupModal');

	if (!modalElement) {
		return;
	}

	if (typeof bootstrap === 'undefined') {
		console.warn('Bootstrap JS is required for the signup modal.');
		return;
	}

	const bootstrapModal = bootstrap.Modal.getOrCreateInstance(modalElement);
	const stepElements = Array.from(modalElement.querySelectorAll('[data-step]'));
	const stepIndicators = Array.from(modalElement.querySelectorAll('[data-step-indicator]'));
	const subtitleElement = modalElement.querySelector('[data-step-subtitle]');
	const errorElement = modalElement.querySelector('[data-step-error]');
	const confirmationEmail = modalElement.querySelector('[data-confirmation-email]');

	const nextButton = modalElement.querySelector('[data-action="next"]');
	const backButton = modalElement.querySelector('[data-action="back"]');

	let currentStep = 1;
	let selectedType = null;

	const subtitles = {
		1: "Choisissez votre profil pour commencer.",
		2: "Renseignez les informations pour finaliser votre inscription.",
		3: "Vérifiez votre boîte mail pour activer votre compte."
	};

	const updateButtons = () => {
		if (currentStep === 1) {
			backButton.classList.add('d-none');
			nextButton.textContent = "Continuer";
		} else if (currentStep === 2) {
			backButton.classList.remove('d-none');
			nextButton.textContent = "Valider";
		} else {
			backButton.classList.add('d-none');
			nextButton.textContent = "Fermer";
		}
	};

	const showStep = (step) => {
		currentStep = step;

		stepElements.forEach((stepElement) => {
			stepElement.classList.toggle('d-none', Number(stepElement.dataset.step) !== step);
		});

		stepIndicators.forEach((indicator) => {
			const indicatorStep = Number(indicator.dataset.stepIndicator);
			indicator.classList.toggle('active', indicatorStep === step);
			indicator.classList.toggle('completed', indicatorStep < step);
		});

		if (subtitleElement) {
			subtitleElement.textContent = subtitles[step];
		}

		updateButtons();
	};

	const getActiveForm = () => {
		if (!selectedType) {
			return null;
		}

		return modalElement.querySelector(`.signup-form[data-user-type="${selectedType}"]`);
	};

	const showFormForSelectedType = () => {
		const forms = modalElement.querySelectorAll('.signup-form');
		forms.forEach((form) => {
			form.classList.toggle('d-none', form.dataset.userType !== selectedType);
		});
	};

	modalElement.querySelectorAll('input[name="signupUserType"]').forEach((radio) => {
		radio.addEventListener('change', (event) => {
			selectedType = event.target.value;
			errorElement?.classList.add('d-none');
		});
	});

	nextButton.addEventListener('click', () => {
		if (currentStep === 1) {
			if (!selectedType) {
				errorElement?.classList.remove('d-none');
				return;
			}

			showFormForSelectedType();
			showStep(2);
			return;
		}

		if (currentStep === 2) {
			const activeForm = getActiveForm();

			if (activeForm && !activeForm.reportValidity()) {
				return;
			}

			const emailInput = activeForm?.querySelector('input[type="email"]');
			confirmationEmail.textContent = emailInput?.value || "votre adresse.";

			showStep(3);
			return;
		}

		if (currentStep === 3) {
			bootstrapModal.hide();
		}
	});

	backButton.addEventListener('click', () => {
		if (currentStep === 2) {
			showStep(1);
			return;
		}

		if (currentStep === 3) {
			showStep(2);
		}
	});

	modalElement.addEventListener('hidden.bs.modal', () => {
		const forms = modalElement.querySelectorAll('.signup-form');
		forms.forEach((form) => form.reset());

		modalElement.querySelectorAll('input[name="signupUserType"]').forEach((radio) => {
			radio.checked = false;
		});

		selectedType = null;
		errorElement?.classList.add('d-none');
		showStep(1);
	});

	modalElement.addEventListener('shown.bs.modal', () => {
		showStep(1);
		modalElement.querySelector('[name="signupUserType"]')?.focus();

		// Change the URL to /?controller=home&method=signup without reloading
		const newUrl = '/?controller=home&method=signup';
		if (window.location.search !== '?controller=home&method=signup') {
			window.history.replaceState({}, '', newUrl);
		}
	});

	// when close the modal change to /?controller=home&method=afficer
	modalElement.addEventListener('hidden.bs.modal', () => {
		const newUrl = '/?controller=home&method=afficher';
		if (window.location.search !== '?controller=home&method=afficher') {
			window.history.replaceState({}, '', newUrl);
		}
	});
});

