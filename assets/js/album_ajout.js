// On attend que le DOM soit entièrement chargé pour exécuter le script
document.addEventListener('DOMContentLoaded', () => {
    // Déclaration de toutes les constantes pour les éléments du DOM
    const addTrackModal = document.getElementById('addTrackModal');
    const editTrackModal = document.getElementById('editTrackModal');
    const confirmModal = document.getElementById('confirmModal');
    const albumForm = document.getElementById('albumForm');
    const albumInput = document.getElementById('albumInput');
    const albumTitleDisplay = document.getElementById('albumTitleDisplay');
    const trackList = document.getElementById('trackList');
    const pochetteInput = document.getElementById('pochette_album');
    const pochettePreview = document.getElementById('pochettePreview');
    const pochetteIconContainer = document.querySelector('.w-48.h-48.bg-white');
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toastMessage');

    // Variables pour suivre l'état
    let trackCount = 0;
    let tracksData = []; // Pour stocker les infos et les fichiers

    // --- GESTIONNAIRES D'ÉVÉNEMENTS ---

    if (albumInput) {
        albumInput.addEventListener('input', (e) => {
            const val = e.target.value;
            albumTitleDisplay.textContent = val || "Nom de l'album/single";
        });
    }

    if (pochetteInput) {
        pochetteInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    pochettePreview.src = e.target.result;
                    pochettePreview.classList.remove('hidden');
                    // On cache seulement l'icône, pas tout le conteneur
                    if (pochetteIconContainer) pochetteIconContainer.querySelector('i').classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        });
    }

    const trackFileInput = document.getElementById('trackFile');
    if (trackFileInput) {
        trackFileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (!file) return;

            jsmediatags.read(file, {
                onSuccess: function(tag) {
                    document.getElementById('trackTitle').value = tag.tags.title || file.name.replace(/\.[^/.]+$/, "");
                    document.getElementById('trackGenre').value = tag.tags.genre || '';
                },
                onError: function(error) {
                    console.log('Error reading tags:', error.type, error.info);
                    document.getElementById('trackTitle').value = file.name.replace(/\.[^/.]+$/, "");
                }
            });

            const audio = new Audio();
            audio.src = URL.createObjectURL(file);
            audio.addEventListener('loadedmetadata', () => {
                document.getElementById('trackDuration').value = Math.round(audio.duration);
                URL.revokeObjectURL(audio.src);
            });
        });
    }

    // --- FONCTIONS GLOBALES (attachées à window pour être appelées depuis le HTML) ---

    window.openAddTrackModal = function() {
        addTrackModal.classList.remove('hidden');
        addTrackModal.classList.add('flex');
    }

    window.closeAddTrackModal = function() {
        addTrackModal.classList.add('hidden');
        addTrackModal.classList.remove('flex');
        document.getElementById('trackFile').value = '';
        document.getElementById('trackTitle').value = '';
        document.getElementById('trackGenre').value = '';
        document.getElementById('trackDuration').value = '';
    }

    window.addTrackToList = function() {
        const fileInput = document.getElementById('trackFile');
        const title = document.getElementById('trackTitle').value;
        const genre = document.getElementById('trackGenre').value;
        const duration = document.getElementById('trackDuration').value;

        if (!fileInput.files[0] || !title) {
            showToast("Veuillez sélectionner un fichier et renseigner le titre.");
            return;
        }

        if (trackCount === 0 && albumInput.value.trim() === '') {
            albumInput.value = title;
            albumTitleDisplay.textContent = title;
        }

        if (trackCount === 0) {
            document.getElementById('no-tracks').style.display = 'none';
        }

        trackCount++;
        tracksData.push({
            file: fileInput.files[0],
            title: title,
            genre: genre,
            duration: duration
        });

        const row = document.createElement('div');
        const bgClass = trackCount % 2 !== 0 ? 'bg-paaxio-salmon/30' : 'bg-paaxio-salmon/20';
        row.className = `grid grid-cols-12 gap-4 px-6 py-3 border-b border-paaxio-salmon/20 ${bgClass} hover:bg-paaxio-salmon/40 transition animate-fade-in`;
        const num = trackCount < 10 ? `0${trackCount}` : trackCount;
        const durationFormatted = new Date(duration * 1000).toISOString().substr(14, 5);

        row.id = `track-row-${trackCount - 1}`;
        row.innerHTML = `
            <div class="col-span-1 text-white font-bold">${num}</div>
            <div class="col-span-8 text-white font-medium">${title}</div>
            <div class="col-span-2 text-right text-white">${durationFormatted}</div>
            <div class="col-span-1 text-right"><button type="button" onclick="openEditTrackModal(${trackCount - 1})" class="text-white hover:text-paaxio-salmonDark"><i class="fas fa-pencil-alt"></i></button></div>
        `;
        trackList.appendChild(row);

        const hiddenInputs = `
            <input type="hidden" name="chansons[${trackCount-1}][titre]" value="${title}">
            <input type="hidden" name="chansons[${trackCount-1}][genre]" value="${genre}">
            <input type="hidden" name="chansons[${trackCount-1}][duree]" value="${duration}">
        `;
        albumForm.insertAdjacentHTML('beforeend', hiddenInputs);

        closeAddTrackModal();
        showToast("Titre ajouté à la liste !");
    }

    window.openEditTrackModal = function(index) {
        const track = tracksData[index];
        document.getElementById('editTrackIndex').value = index;
        document.getElementById('trackTitleEdit').value = track.title;
        document.getElementById('trackGenreEdit').value = track.genre;
        editTrackModal.classList.remove('hidden');
        editTrackModal.classList.add('flex');
    }

    window.closeEditTrackModal = function() {
        editTrackModal.classList.add('hidden');
        editTrackModal.classList.remove('flex');
    }

    window.saveTrackChanges = function() {
        const index = document.getElementById('editTrackIndex').value;
        const newTitle = document.getElementById('trackTitleEdit').value;
        const newGenre = document.getElementById('trackGenreEdit').value;

        if (!newTitle) {
            showToast("Le titre ne peut pas être vide.");
            return;
        }

        tracksData[index].title = newTitle;
        tracksData[index].genre = newGenre;

        const trackRow = document.getElementById(`track-row-${index}`);
        trackRow.querySelector('.col-span-8').textContent = newTitle;

        document.querySelector(`input[name="chansons[${index}][titre]"]`).value = newTitle;
        document.querySelector(`input[name="chansons[${index}][genre]"]`).value = newGenre;

        closeEditTrackModal();
        showToast("Modifications enregistrées !");
    }

    window.confirmSave = function() {
        if (!albumInput.value || !pochetteInput.files[0] || trackCount === 0) {
            showToast("Veuillez renseigner le titre, la pochette et au moins une chanson.");
            return;
        }
        confirmModal.classList.remove('hidden');
        confirmModal.classList.add('flex');
    }

    window.closeConfirmModal = function() {
        confirmModal.classList.add('hidden');
        confirmModal.classList.remove('flex');
    }

    window.submitForm = function() {
        const formData = new FormData(albumForm);
        tracksData.forEach((track, index) => {
            formData.append(`chansons_files[${index}]`, track.file, track.file.name);
        });
        albumForm.submit();
    }

    function showToast(message) {
        toastMsg.textContent = message;
        toast.classList.remove('translate-y-20', 'opacity-0');
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
        }, 3000);
    }
});