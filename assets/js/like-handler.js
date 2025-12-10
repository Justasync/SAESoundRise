// Fonction pour gérer le like des chansons
window.toggleLikeInline = function(event, chansonId) {
    event.stopPropagation();
    console.log('toggleLikeInline called for', chansonId);
    const btn = (event.currentTarget) ? event.currentTarget : event.target.closest('.like-btn');
    const icon = btn ? btn.querySelector('i') : null;
    
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
        console.log('toggleLikeInline response', status, data);
        if (data.liked) {
            if (icon) {
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill');
                icon.classList.add('text-danger');
            }
        } else {
            if (icon) {
                icon.classList.remove('bi-heart-fill');
                icon.classList.add('bi-heart');
                icon.classList.remove('text-danger');
            }
        }
    })
    .catch(err => console.error('Erreur toggleLikeInline:', err));
};
