/**
 * @file battle_logic.js
 * @brief Gestion des modales et des invitations de battle en JavaScript.
 */

let artisteSelectionne = { email: '', pseudo: '' };

/**
 * Affiche la modale de sélection d'un artiste.
 */
function ouvrirModalSelection() {
    const modal = document.getElementById('modalSelection');
    if (modal) modal.style.display = 'flex';
}

/**
 * S'exécute lors du clic sur une ligne de la liste des artistes.
 * @param {HTMLElement} ligne La ligne du tableau cliquée.
 * @param {string} email L'email de l'artiste sélectionné.
 * @param {string} pseudo Le pseudonyme de l'artiste sélectionné.
 */
function selectionnerArtiste(ligne, email, pseudo) {
    // On enregistre les données dans l'objet global
    artisteSelectionne = { email: email, pseudo: pseudo };
    
    // Gestion visuelle de la sélection (décocher les autres radios)
    document.querySelectorAll('input[name="artisteRadio"]').forEach(radio => radio.checked = false);
    const radio = ligne.querySelector('input');
    if (radio) radio.checked = true;
}

/**
 * S'exécute lors du clic sur le bouton "Sélectionner" de la liste.
 * Prépare la modale de confirmation.
 */
function ouvrirModalConfirmation() {
    if (!artisteSelectionne.email) {
        alert("Veuillez sélectionner un artiste dans la liste.");
        return;
    }
    
    // On insère le nom dans le <span> de la modale de confirmation
    const spanNom = document.getElementById('nomArtisteConfirm');
    if (spanNom) {
        spanNom.innerText = artisteSelectionne.pseudo;
    }
    
    // On ferme la sélection et on ouvre la confirmation
    const modalSel = document.getElementById('modalSelection');
    const modalConf = document.getElementById('modalConfirmation');
    
    if (modalSel) modalSel.style.display = 'none';
    if (modalConf) modalConf.style.display = 'flex';
}

/**
 * Envoie l'invitation de battle au serveur via une requête AJAX.
 */
function envoyerInvitation() {
    if (!artisteSelectionne.email) return;

    const donnees = new URLSearchParams();
    donnees.append('emailInvite', artisteSelectionne.email);
    donnees.append('pseudoInvite', artisteSelectionne.pseudo);

    // Appel au contrôleur frontal index.php
    fetch('?controller=battle&method=inviter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: donnees
    })
    .then(reponse => {
        // On vérifie si la réponse du serveur est correcte (Code 200)
        if (!reponse.ok) {
            throw new Error("Le serveur a renvoyé une erreur " + reponse.status);
        }
        return reponse.json();
    })
    .then(data => {
        if (data.status === 'success') {
            // On ferme la modale de confirmation
            const modalConf = document.getElementById('modalConfirmation');
            if (modalConf) modalConf.style.display = 'none';
            
            // Redirection vers le tableau de bord pour voir la nouvelle battle
            window.location.href = 'i?controller=battle&method=gestionDashboard';
        } else {
            alert("Erreur : " + (data.message || "Impossible d'envoyer l'invitation."));
        }
    })
    .catch(error => {
        console.error("Erreur complète :", error);
        alert("Erreur de connexion : Vérifiez que votre serveur PHP est bien lancé.");
    });
}

/**
 * Ferme toutes les modales de type "battle-modal".
 */
function fermerModales() {
    document.querySelectorAll('.battle-modal').forEach(modal => {
        modal.style.display = 'none';
    });
}

/**
 * Alias pour assurer la compatibilité avec les appels dans le HTML.
 */
function ouvrirModalChanson(idBattle) {
    const inputId = document.getElementById('idBattlePourChanson');
    const modalChan = document.getElementById('modalChanson');
    
    if (inputId) inputId.value = idBattle;
    if (modalChan) modalChan.style.display = 'flex';
}

/**
 * Valide le choix de la chanson pour une battle.
 * @param {number} idChanson L'identifiant de la chanson choisie.
 */
function validerChanson(idChanson) {
    const idBattle = document.getElementById('idBattlePourChanson').value;
    const params = new URLSearchParams();
    params.append('idBattle', idBattle);
    params.append('idChanson', idChanson);

    fetch('?controller=battle&method=choisirChanson', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params
    })
    .then(res => {
        if (!res.ok) {
            return res.text().then(text => { throw new Error(text) });
        }
        return res.json();
    })
    .then(data => {
        if(data.status === 'success') {
            window.location.reload();
        } else {
            alert("Erreur: " + data.message);
        }
    })
    .catch(err => {
        console.error("Détails de l'erreur PHP:", err.message);
        alert("Une erreur est survenue. Vérifiez la console pour plus de détails.");
    });
}