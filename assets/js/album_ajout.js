// Array to store track objects (file objects and metadata)
let tracks = [];

// --- Fonctions utilitaires ---

// Puisque la durée n'est plus lue, nous utilisons une durée par défaut ou l'indication d'absence.
function formatDuration(seconds) {
    if (seconds === 0 || isNaN(seconds)) {
        return '00:00'; // Durée par défaut
    }
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.round(seconds % 60);
    return `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
}

// --- Modals Handlers ---

function openAddTrackModal() {
    // Réinitialisation des champs pour une nouvelle piste
    const trackFilesInput = document.getElementById('trackFiles');
    trackFilesInput.value = '';
    const metadataContainer = document.getElementById('track-metadata-container');
    metadataContainer.innerHTML = '';
    
    // Le bouton est toujours actif par défaut
}

function openEditTrackModal(index) {
    const track = tracks[index];
    if (!track) return;
    
    document.getElementById('editTrackIndex').value = index;
    document.getElementById('trackTitleEdit').value = track.title;
    document.getElementById('trackGenreEdit').value = track.genre;

    const modal = new bootstrap.Modal(document.getElementById('editTrackModal'));
    modal.show();
}

function closeEditTrackModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('editTrackModal'));
    if (modal) modal.hide();
}

function confirmSave() {
     if (tracks.length === 0) {
         showToast("Veuillez ajouter au moins un titre.", false);
         return;
     }
    const form = document.getElementById('albumForm');
    if (!form.checkValidity()) {
         form.reportValidity();
         return;
    }
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
}

function closeConfirmModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
    if (modal) modal.hide();
}

// --- Track List Rendering ---

function renderTrackList() {
    const trackListContainer = document.getElementById('trackList');
    
    // Vider complètement le conteneur
    trackListContainer.innerHTML = '';

    if (tracks.length === 0) {
        // Si aucune piste, afficher le message par défaut
        trackListContainer.innerHTML = '<div id="no-tracks" class="text-center py-4 text-secondary">Aucun titre ajouté.</div>';
        return; // Sortir de la fonction
    }

    tracks.forEach((track, index) => {
        const row = document.createElement('div');
        row.className = 'track-row';
        row.setAttribute('data-index', index);
        
        // Track Number
        const num = document.createElement('div');
        num.textContent = (index + 1).toString().padStart(2, '0');
        
        // Track Title
        const title = document.createElement('div');
        title.textContent = track.title;
        
        // Track Duration
        const duration = document.createElement('div');
        duration.className = 'text-end';
        // La durée est affichée par défaut à 00:00
        duration.textContent = formatDuration(track.duration);

        // Track Actions
        const actions = document.createElement('div');
        actions.className = 'track-actions';

        const editIcon = document.createElement('i');
        editIcon.className = 'fas fa-pen-to-square text-secondary';
        editIcon.onclick = (e) => {
            e.stopPropagation();
            openEditTrackModal(index);
        };

        const deleteIcon = document.createElement('i');
        deleteIcon.className = 'fas fa-trash text-secondary';
        deleteIcon.onclick = (e) => {
            e.stopPropagation();
            deleteTrack(index);
        };

        actions.appendChild(editIcon);
        actions.appendChild(deleteIcon);

        row.appendChild(num);
        row.appendChild(title);
        row.appendChild(duration);
        row.appendChild(actions);

        trackListContainer.appendChild(row);
    });
}

// CORRECTION PRINCIPALE : Ajout de la vérification de l'objet File pour éviter le crash.
function updateHiddenInputs() {
    const inputContainer = document.getElementById('albumForm');
    // Supprime les anciens champs
    document.querySelectorAll('#albumForm input[name^="tracks"]').forEach(input => input.remove());

    tracks.forEach((track, index) => {
         // Vérification CRITIQUE : Si le fichier est manquant ou invalide, on saute.
         if (!track.file || !(track.file instanceof File)) {
             console.error(`Erreur: Le fichier pour la piste ${index + 1} est manquant ou invalide.`, track);
             showToast(`Erreur sur la piste ${index + 1} : Fichier audio manquant.`, false);
             return;
         }
         
         // Hidden field for the file itself
         const fileInput = document.createElement('input');
         fileInput.type = 'file';
         fileInput.name = `tracks[${index}][file]`;
         const dataTransfer = new DataTransfer();
         dataTransfer.items.add(track.file);
         fileInput.files = dataTransfer.files;
         fileInput.classList.add('d-none');
         inputContainer.appendChild(fileInput);

         // Hidden field for title
         const titleInput = document.createElement('input');
         titleInput.type = 'hidden';
         titleInput.name = `tracks[${index}][title]`;
         titleInput.value = track.title;
         inputContainer.appendChild(titleInput);

         // Hidden field for genre
         const genreInput = document.createElement('input');
         genreInput.type = 'hidden';
         genreInput.name = `tracks[${index}][genre]`;
         genreInput.value = track.genre;
         inputContainer.appendChild(genreInput);

         // Hidden field for duration
         const durationInput = document.createElement('input');
         durationInput.type = 'hidden';
         durationInput.name = `tracks[${index}][duration]`;
         durationInput.value = track.duration || 0; 
         inputContainer.appendChild(durationInput);
    });
}

function deleteTrack(index) {
    tracks.splice(index, 1);
    renderTrackList();
    // Synchroniser les champs cachés après suppression
    updateHiddenInputs(); 
    showToast("Titre supprimé.", true);
}

function clearHiddenTrackInputs() {
    document.querySelectorAll('#albumForm input[name^="tracks"]').forEach(input => input.remove());
}

// --- Track Addition Logic ---

function addTracksToList() {
    const metadataContainer = document.getElementById('track-metadata-container');
    const trackForms = metadataContainer.querySelectorAll('.p-3');
    let allValid = true;
    let tracksToAdd = [];

    if (trackForms.length === 0) {
        showToast("Veuillez sélectionner au moins un fichier audio.", false);
        return;
    }

    trackForms.forEach(form => {
        const title = form.querySelector('.track-title').value.trim();
        const genre = form.querySelector('.track-genre').value.trim();
        const file = form.file; // Le fichier est attaché à l'élément de formulaire

        if (!title) {
            allValid = false;
        }

        tracksToAdd.push({
            file: file,
            title: title,
            genre: genre,
            duration: 0 // Durée fixée
        });
    });

    if (!allValid) {
        showToast("Veuillez renseigner un titre pour chaque chanson.", false);
        return;
    }

    tracks.push(...tracksToAdd);

    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('addTrackModal'));
    if (modalInstance) modalInstance.hide();

    renderTrackList();
    updateHiddenInputs(); // Synchroniser les champs cachés
    showToast(`${tracksToAdd.length} titre(s) ajouté(s) avec succès.`, true);
}

// --- Track Editing Logic ---

function saveTrackChanges() {
    const index = parseInt(document.getElementById('editTrackIndex').value);
    const newTitle = document.getElementById('trackTitleEdit').value.trim();
    const newGenre = document.getElementById('trackGenreEdit').value.trim();

    if (!newTitle) {
        showToast("Le titre ne peut pas être vide.", false);
        return;
    }

    if (tracks[index]) {
        tracks[index].title = newTitle;
        tracks[index].genre = newGenre;
        
        renderTrackList();
        // CORRECTION/AJOUT : Synchroniser les champs cachés immédiatement
        updateHiddenInputs();
        
        // Déplacer le focus avant de fermer la modale pour éviter l'avertissement aria-hidden
        document.activeElement.blur();

        closeEditTrackModal();
        showToast("Modifications enregistrées.", true);
    } else {
        showToast("Erreur: Impossible de trouver la piste à modifier.", false);
    }
}

// --- Album Art Preview ---

document.getElementById('pochette_album').addEventListener('change', function(event) {
    const [file] = event.target.files;
    const preview = document.getElementById('pochettePreview');
    const plusIcon = document.querySelector('.album-art-box .fa-plus');

    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('d-none');
        plusIcon.classList.add('d-none');
    } else {
        preview.classList.add('d-none');
        preview.src = '';
        plusIcon.classList.remove('d-none');
    }
});

// --- Album Title Input Listener ---

document.getElementById('albumInput').addEventListener('input', function(event) {
    document.getElementById('albumTitleDisplay').textContent = event.target.value || "Nom de l'album/single";
});

// --- Toast Notification Logic ---

function showToast(message, isSuccess) {
    const toastElement = document.getElementById('toast');
    const toastBody = document.getElementById('toastMessage');
    const icon = toastElement.querySelector('.fa-check-circle');

    toastBody.textContent = message;
    
    if (isSuccess) {
        toastElement.classList.remove('bg-danger');
        toastElement.classList.add('bg-success');
        icon.classList.remove('text-danger');
        icon.classList.add('text-success');
    } else {
        toastElement.classList.remove('bg-success');
        toastElement.classList.add('bg-danger');
        icon.classList.remove('text-success');
        icon.classList.add('text-danger');
    }
    
    const toast = new bootstrap.Toast(toastElement);
    toast.show();
}


// --- Final Form Submission ---

function submitForm() {
    closeConfirmModal();
    
    const albumForm = document.getElementById('albumForm');
    
    // Update hidden inputs just before submission
    updateHiddenInputs();

    // In a live environment, you would call: 
    albumForm.submit(); 
    
    // For this environment, we just log and reset:
    console.log("Form submission simulated. Data ready to be sent.");
    
    // Reset state
    setTimeout(() => {
        tracks = [];
        document.getElementById('albumInput').value = '';
        document.getElementById('dateSortieAlbum').value = '';
        document.getElementById('pochette_album').value = '';
        document.getElementById('pochettePreview').classList.add('d-none');
        document.querySelector('.album-art-box .fa-plus').classList.remove('d-none');
        document.getElementById('albumTitleDisplay').textContent = "Nom de l'album/single";
        clearHiddenTrackInputs();
        renderTrackList();
    }, 1000);
}


// --- Initialization ---

document.addEventListener('DOMContentLoaded', function() {
    renderTrackList();
    
    // Correction pour s'assurer que le bouton d'ouverture réinitialise les champs avant que Bootstrap n'ouvre la modale
    const addTrackButton = document.querySelector('[data-bs-target="#addTrackModal"]');
    if (addTrackButton) {
        addTrackButton.addEventListener('click', openAddTrackModal);
    }

    // Gestionnaire pour la sélection de plusieurs fichiers
    document.getElementById('trackFiles').addEventListener('change', function(event) {
        const files = event.target.files;
        const metadataContainer = document.getElementById('track-metadata-container');
        const template = document.getElementById('track-metadata-template');
        metadataContainer.innerHTML = ''; // Vider le conteneur

        Array.from(files).forEach(file => {
            const clone = template.content.cloneNode(true);
            const formElement = clone.querySelector('.p-3');
            
            clone.querySelector('.filename').textContent = file.name;
            
            // Pré-remplir le titre en retirant l'extension du fichier
            const defaultTitle = file.name.replace(/\.[^/.]+$/, "");
            clone.querySelector('.track-title').value = defaultTitle;

            formElement.file = file; // Attacher l'objet fichier à l'élément DOM

            metadataContainer.appendChild(clone);
        });
    });
});