(function () {
    const playlistModalElement = document.getElementById('playlistModal');
    const playlistModalTrackName = document.getElementById('playlistModalTrackName');
    const playlistSelect = document.getElementById('playlist-select');
    const playlistLikedStatus = document.getElementById('playlist-liked-status');
    const playlistModalFeedback = document.getElementById('playlist-modal-feedback');
    const playlistModalConfirm = document.getElementById('playlist-modal-confirm');

    if (!playlistModalElement || !playlistModalConfirm || typeof bootstrap === 'undefined') {
        return;
    }

    const playlistModal = new bootstrap.Modal(playlistModalElement);
    let currentSongIsLiked = false;
    let currentSongIcon = null;
    const defaultConfirmLabel = playlistModalConfirm.textContent;

    const refreshLikedStatus = function () {
        if (!playlistLikedStatus || !playlistSelect) return;

        if (playlistSelect.value === '__LIKED__' && currentSongIsLiked) {
            playlistLikedStatus.textContent = 'Cette chanson est déjà dans Musiques likées. Vous pouvez la retirer.';
            playlistLikedStatus.classList.remove('d-none');
            playlistModalConfirm.textContent = 'Retirer';
            return;
        }

        playlistLikedStatus.textContent = '';
        playlistLikedStatus.classList.add('d-none');
        playlistModalConfirm.textContent = defaultConfirmLabel;
    };

    if (playlistSelect) {
        playlistSelect.addEventListener('change', refreshLikedStatus);
    }

    document.addEventListener('click', function (event) {
        const btn = event.target.closest('.like-btn');
        if (!btn) return;

        event.preventDefault();
        event.stopPropagation();

        const row = btn.closest('tr');
        const titre = row ? (row.querySelector('.track-title')?.textContent || '').trim() : '';
        const chansonId = btn.dataset.id;
        const icon = btn.querySelector('i');

        currentSongIcon = icon;
        currentSongIsLiked = !!(icon && icon.classList.contains('bi-heart-fill'));

        playlistModalConfirm.dataset.chansonId = chansonId;
        if (playlistModalTrackName) {
            playlistModalTrackName.textContent = titre ? `Titre : ${titre}` : 'Titre sélectionné';
        }
        if (playlistModalFeedback) {
            playlistModalFeedback.textContent = '';
            playlistModalFeedback.className = 'playlist-feedback mt-3';
        }
        refreshLikedStatus();
        playlistModal.show();
    });

    playlistModalConfirm.addEventListener('click', function () {
        const chansonId = this.dataset.chansonId;
        const idPlaylist = playlistSelect ? playlistSelect.value : null;
        const shouldRemoveLike = idPlaylist === '__LIKED__' && currentSongIsLiked;

        if (!chansonId || !idPlaylist) {
            if (playlistModalFeedback) {
                playlistModalFeedback.textContent = 'Choisissez une playlist.';
                playlistModalFeedback.className = 'playlist-feedback mt-3 text-danger';
            }
            return;
        }

        this.disabled = true;
        if (playlistModalFeedback) {
            playlistModalFeedback.textContent = shouldRemoveLike ? 'Retrait en cours...' : 'Ajout en cours...';
            playlistModalFeedback.className = 'playlist-feedback mt-3 text-muted';
        }

        const endpoint = shouldRemoveLike
            ? '/?controller=chanson&method=retirerLike'
            : idPlaylist === '__LIKED__'
            ? '/?controller=chanson&method=ajouterLike'
            : '/?controller=playlist&method=ajouterChanson';

        const body = idPlaylist === '__LIKED__'
            ? `idChanson=${encodeURIComponent(chansonId)}`
            : `idPlaylist=${encodeURIComponent(idPlaylist)}&idChanson=${encodeURIComponent(chansonId)}`;

        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        })
            .then(async (res) => {
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('Connectez-vous pour continuer.');
                }
                if (!res.ok) {
                    throw new Error(data.message || 'Erreur lors de l\'ajout.');
                }
                return data;
            })
            .then((data) => {
                if (idPlaylist === '__LIKED__') {
                    currentSongIsLiked = !shouldRemoveLike;
                    if (currentSongIcon) {
                        if (currentSongIsLiked) {
                            currentSongIcon.classList.remove('bi-heart');
                            currentSongIcon.classList.add('bi-heart-fill');
                            currentSongIcon.classList.add('text-danger');
                        } else {
                            currentSongIcon.classList.remove('bi-heart-fill');
                            currentSongIcon.classList.remove('text-danger');
                            currentSongIcon.classList.add('bi-heart');
                        }
                    }
                    refreshLikedStatus();

                    if (shouldRemoveLike
                        && window.location.search.includes('controller=utilisateur')
                        && window.location.search.includes('method=afficherMesLikes')) {
                        const activeBtn = document.querySelector(`.like-btn[data-id="${chansonId}"]`);
                        const row = activeBtn ? activeBtn.closest('tr') : null;
                        if (row) {
                            row.remove();
                        }
                    }
                }

                if (playlistModalFeedback) {
                    playlistModalFeedback.textContent = data.message || 'Chanson ajoutée à la playlist.';
                    playlistModalFeedback.className = 'playlist-feedback mt-3 text-success';
                }

                setTimeout(() => {
                    playlistModal.hide();
                }, 700);
            })
            .catch((err) => {
                if (playlistModalFeedback) {
                    playlistModalFeedback.textContent = err.message || 'Erreur lors de l\'ajout.';
                    playlistModalFeedback.className = 'playlist-feedback mt-3 text-danger';
                }
            })
            .finally(() => {
                this.disabled = false;
            });
    });
})();