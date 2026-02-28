document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const resultsContainer = document.getElementById('searchResults');
    let timeout = null;

    if (!searchInput || !resultsContainer) return;

    searchInput.addEventListener('input', function () {
        clearTimeout(timeout);
        const term = this.value.trim();

        if (term.length < 0) {
            resultsContainer.style.display = 'none';
            resultsContainer.innerHTML = '';
            return;
        }

        timeout = setTimeout(() => {
            fetch(`index.php?controller=search&method=json_search&term=${encodeURIComponent(term)}`)
                .then(response => response.json())
                .then(data => {
                    resultsContainer.innerHTML = '';
                    let hasResults = false;

                    const addHeader = (text) => {
                        const header = document.createElement('div');
                        header.className = 'list-group-item list-group-item-secondary fw-bold';
                        header.textContent = text;
                        resultsContainer.appendChild(header);
                    };

                    // ARTISTES
                    if (data.artistes && data.artistes.length > 0) {
                        hasResults = true;
                        addHeader('Artistes');
                        data.artistes.forEach(a => {
                            const item = document.createElement('a');
                            item.href = `index.php?controller=utilisateur&method=afficherProfilArtiste&pseudo=${a.pseudo}`;
                            item.className = 'list-group-item list-group-item-action d-flex align-items-center';
                            item.innerHTML = `<img src="${a.image}" style="width:30px; height:30px; border-radius:50%; margin-right:10px;"> <span>${a.pseudo}</span>`;
                            resultsContainer.appendChild(item);
                        });
                    }

                    // ALBUMS
                    if (data.albums && data.albums.length > 0) {
                        hasResults = true;
                        addHeader('Albums');
                        data.albums.forEach(a => {
                            const item = document.createElement('a');
                            item.href = `index.php?controller=album&method=afficherDetails&idAlbum=${a.id}`;
                            item.className = 'list-group-item list-group-item-action d-flex align-items-center';
                            item.innerHTML = `
                                <img src="${a.image}" style="width:30px; height:30px; border-radius:4px; margin-right:10px;" onerror="this.src='assets/images/albums/default.png'">
                                <div>
                                    <h6 class="mb-0" style="font-size: 0.9rem;">${a.titre}</h6>
                                    <small class="text-muted">${a.artiste}</small>
                                </div>
                            `;
                            resultsContainer.appendChild(item);
                        });
                    }

                    // CHANSONS
                    if (data.chansons && data.chansons.length > 0) {
                        hasResults = true;
                        addHeader('Chansons');
                        data.chansons.forEach(c => {
                            const item = document.createElement('a');
                            if (c.idAlbum && c.id) {
                                // Redirige vers la page de l'album en mettant en avant la chanson via son id
                                item.href = `index.php?controller=album&method=afficherDetails&idAlbum=${c.idAlbum}&idChanson=${c.id}`;
                            } else if (c.idAlbum) {
                                item.href = `index.php?controller=album&method=afficherDetails&idAlbum=${c.idAlbum}`;
                            } else {
                                item.href = "#";
                            }
                            item.className = 'list-group-item list-group-item-action';
                            item.innerHTML = `
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-0" style="font-size: 0.9rem;">${c.titre}</h6>
                                    <small class="text-muted">${c.ecoutes} écoutes</small>
                                </div>
                            `;
                            resultsContainer.appendChild(item);
                        });
                    }

                    resultsContainer.style.display = hasResults ? 'block' : 'none';
                });
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target)) resultsContainer.style.display = 'none';
    });
});