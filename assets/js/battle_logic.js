let artisteSelectionne = { email: '', pseudo: '' };

function abrirModalSelection() {
    document.getElementById('modalSelection').style.display = 'flex';
}

/**
 * Se ejecuta al hacer clic en una fila de la lista de artistas
 */
function selectionnerArtiste(ligne, email, pseudo) {
    // Guardamos los datos en el objeto global
    artisteSelectionne = { email: email, pseudo: pseudo };
    
    // Gestión visual de la selección
    document.querySelectorAll('input[name="artisteRadio"]').forEach(radio => radio.checked = false);
    const radio = ligne.querySelector('input');
    if (radio) radio.checked = true;
}

/**
 * Se ejecuta al darle a "Sélectionner" en la lista de artistas
 */
function abrirModalConfirmation() {
    if (!artisteSelectionne.email) {
        alert("Veuillez sélectionner un artiste dans la liste.");
        return;
    }
    
    // Insertamos el nombre en el <span> de la modal de confirmación
    const spanNombre = document.getElementById('nomArtisteConfirm');
    if (spanNombre) {
        spanNombre.innerText = artisteSelectionne.pseudo;
    }
    
    // Cerramos la selección y abrimos la confirmación
    document.getElementById('modalSelection').style.display = 'none';
    document.getElementById('modalConfirmation').style.display = 'flex';
}

/**
 * ESTE ES EL BOTÓN "CONFIRMER" QUE NO TE FUNCIONABA
 */
function envoyerInvitation() {
    if (!artisteSelectionne.email) return;

    const donnees = new URLSearchParams();
    donnees.append('emailInvitado', artisteSelectionne.email);
    donnees.append('pseudoInvitado', artisteSelectionne.pseudo);

    // CAMBIO AQUÍ: Añadimos 'index.php' antes del signo '?' 
    // Esto asegura que la petición vaya al controlador frontal.
    fetch('index.php?controller=battle&method=inviter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: donnees
    })
    .then(reponse => {
        // Verificamos si la respuesta es correcta antes de parsear el JSON
        if (!reponse.ok) {
            throw new Error("Le serveur a renvoyé une erreur " + reponse.status);
        }
        return reponse.json();
    })
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('modalConfirmation').style.display = 'none';
            // Redirigir al dashboard para ver la nueva battle
            window.location.href = 'index.php?controller=battle&method=gestionDashboard';
        } else {
            alert("Erreur: " + (data.message || "Impossible d'envoyer l'invitation"));
        }
    })
    .catch(error => {
        console.error("Erreur complète:", error);
        alert("Erreur de connexion : Vérifie que ton serveur PHP est bien lancé.");
    });
}

function cerrarModales() {
    document.querySelectorAll('.battle-modal').forEach(modal => {
        modal.style.display = 'none';
    });
}

// Alias para tus funciones en el HTML
function fermerModales() { cerrarModales(); }
function ouvrirModalSelection() { abrirModalSelection(); }
function ouvrirModalConfirmation() { abrirModalConfirmation(); }