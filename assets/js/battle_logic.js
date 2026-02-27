let artisteSelectionne = { email: '', pseudo: '' };

function ouvrirModalSelection() {
    document.getElementById('modalSelection').style.display = 'flex';
}

function selectionnerArtiste(ligne, email, pseudo) {
    artisteSelectionne = { email: email, pseudo: pseudo };
    // Gestion visuelle de la sélection
    document.querySelectorAll('input[name="artisteRadio"]').forEach(radio => radio.checked = false);
    ligne.querySelector('input').checked = true;
}

function ouvrirModalConfirmation() {
    if (!artisteSelectionne.email) {
        alert("Veuillez sélectionner un artiste dans la liste.");
        return;
    }
    document.getElementById('nomArtisteConfirm').innerText = artisteSelectionne.pseudo;
    document.getElementById('modalSelection').style.display = 'none';
    document.getElementById('modalConfirmation').style.display = 'flex';
}

function envoyerInvitation() {
    const donnees = new URLSearchParams();
    donnees.append('emailInvitado', artisteSelectionne.email);
    donnees.append('pseudoInvitado', artisteSelectionne.pseudo);

    fetch('?controller=battle&method=inviter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: donnees
    })
    .then(reponse => reponse.json())
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('modalConfirmation').style.display = 'none';
            document.getElementById('modalSucces').style.display = 'flex';
        } else {
            alert("Une erreur est survenue lors de l'envoi de l'invitation.");
        }
    });
}

function fermerModales() {
    document.querySelectorAll('.battle-modal').forEach(modal => {
        modal.style.display = 'none';
    });
}

function jouerAudio(idChanson) {
    // Logique pour jouer la chanson via AJAX ou balise audio
    console.log("Lecture de la chanson ID : " + idChanson);
}