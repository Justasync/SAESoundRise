/**
 * @file like-handler.js
 * @brief Gestionnaire des interactions de like sur les chansons
 * 
 * @description Ce fichier contient la logique pour gérer les likes des chansons
 * via des requêtes AJAX. Il permet de basculer l'état "aimé" d'une chanson
 * et met à jour l'interface utilisateur en conséquence.
 */

/**
 * @brief Bascule l'état "aimé" d'une chanson
 * 
 * @details Cette fonction est appelée lorsqu'un utilisateur clique sur le bouton
 * de like d'une chanson. Elle envoie une requête POST au serveur pour
 * basculer l'état du like et met à jour l'icône en fonction de la réponse.
 * 
 * @param {Event} event - L'événement de clic déclenché
 * @param {number|string} chansonId - L'identifiant unique de la chanson
 * 
 * @returns {void}
 * 
 * @example
 * // Utilisation dans le HTML :
 * // <button onclick="toggleLikeInline(event, 123)">Like</button>
 */
window.toggleLikeInline = function(event, chansonId) {
    // Empêche la propagation de l'événement aux éléments parents
    event.stopPropagation();
    console.log('toggleLikeInline appelé pour', chansonId);
    
    // Récupération du bouton et de l'icône associés
    const btn = (event.currentTarget) ? event.currentTarget : event.target.closest('.like-btn');
    const icon = btn ? btn.querySelector('i') : null;
    
    // Envoi de la requête AJAX au serveur
    fetch('/?controller=chanson&method=toggleLike', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `idChanson=${encodeURIComponent(chansonId)}`
    })
    .then(async (res) => {
        const text = await res.text();
        try {
            const data = JSON.parse(text);
            return { ok: res.ok, status: res.status, data };
        } catch (err) {
            console.error('Réponse non JSON du serveur pour toggleLike:', text);
            throw err;
        }
    })
    .then(({ status, data }) => {
        console.log('Réponse toggleLikeInline', status, data);
        
        // Mise à jour de l'icône selon l'état du like
        if (data.liked) {
            // Chanson aimée : affiche un cœur plein rouge
            if (icon) {
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill');
                icon.classList.add('text-danger');
            }
        } else {
            // Chanson non aimée : affiche un cœur vide
            if (icon) {
                icon.classList.remove('bi-heart-fill');
                icon.classList.add('bi-heart');
                icon.classList.remove('text-danger');
            }
        }
    })
    .catch(err => console.error('Erreur toggleLikeInline:', err));
};
