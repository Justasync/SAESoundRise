<?php

class PlaylistDAO {
    private ?PDO $pdo;

    public function __construct(?PDO $pdo) {
        $this->pdo = $pdo;
    }
    public function find(?int $idPlaylist): ?Playlist {
        $sql = "SELECT * FROM playlist WHERE idPlaylist = :idPlaylist";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':idPlaylist', $idPlaylist, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Playlist(
                $row['idPlaylist'],
                $row['nomPlaylist'],
                (bool)$row['estPubliquePlaylist'],
                new DateTime($row['dateCreationPlaylist']),
                new DateTime($row['dateDerniereModification']),
                $row['emailProprietaire']
            );
        }
        return null;
    }

    /**
     * Get the value of pdo
     */
    public function getPdo(): ?PDO
    {
        return $this->pdo;
    }
    /**
     * Set the value of pdo
     *
     */
    public function setPdo(?PDO $pdo): void
    {
        $this->pdo = $pdo;
    }
}