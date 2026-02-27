<?php

/**
 * @file controller_search.class.php
 * @brief Controller for handling search requests via AJAX
 */
class ControllerSearch extends Controller
{
    /**
     * @brief Constructeur du contrôleur search.
     * 
     * @param \Twig\Environment $twig Environnement Twig pour le rendu des templates.
     * @param \Twig\Loader\FilesystemLoader $loader Chargeur de fichiers Twig.
     */
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    /**
     * @brief Retourne les résultats de la recherche au format JSON.
     */
    public function json_search()
    {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');

        $term = $_GET['term'] ?? '';
        $term = trim($term);
        
        $results = [
            'artistes' => [],
            'albums' => [],
            'chansons' => []
        ];

        try {
            if (!empty($term)) {
                $pdo = $this->getPDO();

                // --- ARTISTES ---
                $utilisateurDAO = new UtilisateurDAO($pdo);
                $artistes = $utilisateurDAO->rechercher($term);
                if (is_array($artistes)) {
                    foreach ($artistes as $artiste) {
                        $results['artistes'][] = [
                            'pseudo' => $artiste->getPseudoUtilisateur(),
                            'image' => $artiste->geturlPhotoUtilisateur() ?: "assets/images/profile_pictures/default.png"
                        ];
                    }
                }

                // --- ALBUMS ---
                $albumDAO = new AlbumDAO($pdo); 
                $listaAlbums = $albumDAO->rechercher($term); 
                if (is_array($listaAlbums)) {
                    foreach ($listaAlbums as $album) {
                        $img = $album->geturlPochetteAlbum();
                        $path = $img ? ltrim($img, '/') : "assets/images/albums/default.png";

                        $results['albums'][] = [
                            'id'      => $album->getIdAlbum(),
                            'titre'   => $album->getTitreAlbum(),
                            'image'   => $path,
                            'artiste' => $album->getPseudoArtiste()
                        ];  
                    }
                }

                // --- CHANSONS ---
                $chansonDAO = new ChansonDAO($pdo);
                $listaChansons = $chansonDAO->rechercherParTitre($term);
                if (is_array($listaChansons)) {
                    foreach ($listaChansons as $chanson) {
                        $albumObj = $chanson->getAlbumChanson();
                        $idAlbum = ($albumObj && method_exists($albumObj, 'getIdAlbum')) ? $albumObj->getIdAlbum() : null;

                        $results['chansons'][] = [
                            'id'      => $chanson->getIdChanson(),
                            'titre'   => $chanson->getTitrechanson(),
                            'ecoutes' => $chanson->getNbecoutechanson(),
                            'idAlbum' => $idAlbum,
                        ];
                    }
                }
            }

            ob_clean();
            echo json_encode($results);

        } catch (Exception $e) {
            ob_clean();
            echo json_encode(['error' => $e->getMessage()]);
        }
        
        exit; 
    }
}