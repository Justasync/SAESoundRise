<?php
/**
 * @file modeles/battle.class.php
 * @brief Classe représentant une battle musicale
 */

/**
 * @enum StatutBattle Enumération des statuts possibles pour une battle.
 * @case En_attente La battle est en attente de démarrage.
 * @case En_cours La battle est en cours.
 * @case Terminee La battle est terminée.
 * @case Annulee La battle a été annulée.
 */
enum StatutBattle: string {
    case En_attente = 'en_attente';
    case En_cours = 'en_cours';
    case Terminee = 'terminee';
    case Annulee = 'annulee';
}

/**
 * @class Battle
 * @brief Classe représentant une battle musicale.
 * 
 * Cette classe contient toutes les informations relatives à un duel entre deux artistes,
 * incluant les identifiants des participants, leurs chansons et les objets hydratés
 * pour un affichage sécurisé.
 */
class Battle {
    /**
     * @var int|null $idBattle L'identifiant unique de la battle.
     */
    private int|null $idBattle;

    /**
     * @var string|null $titreBattle Le titre de la battle.
     */
    private string|null $titreBattle;

    /**
     * @var DateTime|null $dateDebutBattle La date de début de la battle.
     */
    private DateTime|null $dateDebutBattle;

    /**
     * @var DateTime|null $dateFinBattle La date de fin de la battle.
     */
    private DateTime|null $dateFinBattle;

    /**
     * @var StatutBattle|null $statutBattle Le statut actuel de la battle.
     */
    private StatutBattle|null $statutBattle;

    /**
     * @var string|null $emailCreateurBattle L'email du créateur de la battle.
     */
    private string|null $emailCreateurBattle;

    /**
     * @var string|null $emailParticipantBattle L'email du participant à la battle.
     */
    private string|null $emailParticipantBattle;

    /**
     * @var int|null $idChansonCreateur L'identifiant de la chanson choisie par le créateur.
     */
    private int|null $idChansonCreateur;

    /**
     * @var int|null $idChansonParticipant L'identifiant de la chanson choisie par le participant.
     */
    private int|null $idChansonParticipant;

    /**
     * @var Utilisateur|null $createur Objet Utilisateur représentant le créateur (pour affichage sécurisé du pseudo).
     */
    private ?Utilisateur $createur = null;

    /**
     * @var Utilisateur|null $participant Objet Utilisateur représentant le participant (pour affichage sécurisé du pseudo).
     */
    private ?Utilisateur $participant = null;

    /**
     * @var Chanson|null $chansonCreateurObj Objet Chanson représentant la musique du créateur.
     */
    private ?Chanson $chansonCreateurObj = null;

    /**
     * @var Chanson|null $chansonParticipantObj Objet Chanson représentant la musique du participant.
     */
    private ?Chanson $chansonParticipantObj = null;

    /**
     * @brief Constructeur de la classe Battle.
     * @param int|null $idBattle L'identifiant unique de la battle.
     * @param string|null $titreBattle Le titre de la battle.
     * @param DateTime|null $dateDebutBattle La date de début de la battle.
     * @param DateTime|null $dateFinBattle La date de fin de la battle.
     * @param StatutBattle|null $statutBattle Le statut actuel de la battle.
     * @param string|null $emailCreateurBattle L'email du créateur de la battle.
     * @param string|null $emailParticipantBattle L'email du participant à la battle.
     * @param int|null $idChansonCreateur L'identifiant de la chanson du créateur.
     * @param int|null $idChansonParticipant L'identifiant de la chanson du participant.
     */
    public function __construct(?int $idBattle = null, ?string $titreBattle = null, ?DateTime $dateDebutBattle = null, 
                                ?DateTime $dateFinBattle = null, ?StatutBattle $statutBattle = null, 
                                ?string $emailCreateurBattle = null, ?string $emailParticipantBattle = null,
                                ?int $idChansonCreateur = null, ?int $idChansonParticipant = null)
    {
        $this->idBattle = $idBattle;
        $this->titreBattle = $titreBattle;
        $this->dateDebutBattle = $dateDebutBattle;
        $this->dateFinBattle = $dateFinBattle;
        $this->statutBattle = $statutBattle;
        $this->emailCreateurBattle = $emailCreateurBattle;
        $this->emailParticipantBattle = $emailParticipantBattle;
        $this->idChansonCreateur = $idChansonCreateur;
        $this->idChansonParticipant = $idChansonParticipant;
    }

    /**
     * @brief Getter pour idBattle
     * @return int|null
     */
    public function getIdBattle(): ?int
    {
        return $this->idBattle;
    }

    /**
     * @brief Setter pour idBattle
     * @param int|null $idBattle
     * @return void
     */
    public function setIdBattle(?int $idBattle): void
    {
        $this->idBattle = $idBattle;
    }

    /**
     * @brief Getter pour titreBattle
     * @return string|null
     */
    public function getTitreBattle(): ?string
    {
        return $this->titreBattle;
    }

    /**
     * @brief Setter pour titreBattle
     * @param string|null $titreBattle
     * @return void
     */
    public function setTitreBattle(?string $titreBattle): void
    {
        $this->titreBattle = $titreBattle;
    }

    /**
     * @brief Getter pour dateDebutBattle
     * @return DateTime|null
     */
    public function getDateDebutBattle(): ?DateTime
    {
        return $this->dateDebutBattle;
    }

    /**
     * @brief Setter pour dateDebutBattle
     * @param DateTime|null $dateDebutBattle
     * @return void
     */
    public function setDateDebutBattle(?DateTime $dateDebutBattle): void
    {
        $this->dateDebutBattle = $dateDebutBattle;
    }

    /**
     * @brief Getter pour dateFinBattle
     * @return DateTime|null
     */
    public function getDateFinBattle(): ?DateTime
    {
        return $this->dateFinBattle;
    }

    /**
     * @brief Setter pour dateFinBattle
     * @param DateTime|null $dateFinBattle
     * @return void
     */
    public function setDateFinBattle(?DateTime $dateFinBattle): void
    {
        $this->dateFinBattle = $dateFinBattle;
    }

    /**
     * @brief Getter pour statutBattle
     * @return StatutBattle|null
     */
    public function getStatutBattle(): ?StatutBattle
    {
        return $this->statutBattle;
    }

    /**
     * @brief Setter pour statutBattle
     * @param StatutBattle|null $statutBattle
     * @return void
     */
    public function setStatutBattle(?StatutBattle $statutBattle): void
    {
        $this->statutBattle = $statutBattle;
    }

    /**
     * @brief Getter pour emailCreateurBattle
     * @return string|null
     */
    public function getEmailCreateurBattle(): ?string
    {
        return $this->emailCreateurBattle;
    }

    /**
     * @brief Setter pour emailCreateurBattle
     * @param string|null $emailCreateurBattle
     * @return void
     */
    public function setEmailCreateurBattle(?string $emailCreateurBattle): void
    {
        $this->emailCreateurBattle = $emailCreateurBattle;
    }

    /**
     * @brief Getter pour emailParticipantBattle
     * @return string|null
     */
    public function getEmailParticipantBattle(): ?string
    {
        return $this->emailParticipantBattle;
    }

    /**
     * @brief Setter pour emailParticipantBattle
     * @param string|null $emailParticipantBattle
     * @return void
     */
    public function setEmailParticipantBattle(?string $emailParticipantBattle): void
    {
        $this->emailParticipantBattle = $emailParticipantBattle;
    }

    /**
     * @brief Getter pour idChansonCreateur
     * @return int|null
     */
    public function getIdChansonCreateur(): ?int { return $this->idChansonCreateur; }

    /**
     * @brief Setter pour idChansonCreateur
     * @param int|null $id
     * @return void
     */
    public function setIdChansonCreateur(?int $id): void { $this->idChansonCreateur = $id; }

    /**
     * @brief Getter pour idChansonParticipant
     * @return int|null
     */
    public function getIdChansonParticipant(): ?int { return $this->idChansonParticipant; }

    /**
     * @brief Setter pour idChansonParticipant
     * @param int|null $id
     * @return void
     */
    public function setIdChansonParticipant(?int $id): void { $this->idChansonParticipant = $id; }

    /**
     * @brief Getter pour l'objet Utilisateur créateur
     * @return Utilisateur|null
     */
    public function getCreateur(): ?Utilisateur { return $this->createur; }

    /**
     * @brief Setter pour l'objet Utilisateur créateur
     * @param Utilisateur|null $u
     * @return void
     */
    public function setCreateur(?Utilisateur $u): void { $this->createur = $u; }

    /**
     * @brief Getter pour l'objet Utilisateur participant
     * @return Utilisateur|null
     */
    public function getParticipant(): ?Utilisateur { return $this->participant; }

    /**
     * @brief Setter pour l'objet Utilisateur participant
     * @param Utilisateur|null $u
     * @return void
     */
    public function setParticipant(?Utilisateur $u): void { $this->participant = $u; }

    /**
     * @brief Getter pour l'objet Chanson du créateur
     * @return Chanson|null
     */
    public function getChansonCreateurObj(): ?Chanson { return $this->chansonCreateurObj; }

    /**
     * @brief Setter pour l'objet Chanson du créateur
     * @param Chanson|null $c
     * @return void
     */
    public function setChansonCreateurObj(?Chanson $c): void { $this->chansonCreateurObj = $c; }

    /**
     * @brief Getter pour l'objet Chanson du participant
     * @return Chanson|null
     */
    public function getChansonParticipantObj(): ?Chanson { return $this->chansonParticipantObj; }

    /**
     * @brief Setter pour l'objet Chanson du participant
     * @param Chanson|null $c
     * @return void
     */
    public function setChansonParticipantObj(?Chanson $c): void { $this->chansonParticipantObj = $c; }
}
?>