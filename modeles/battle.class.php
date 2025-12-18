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
 * Classe représentant une battle musicale.
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
     * Constructeur de la classe Battle.
     * @param int|null $idBattle L'identifiant unique de la battle.
     * @param string|null $titreBattle Le titre de la battle.
     * @param DateTime|null $dateDebutBattle La date de début de la battle.
     * @param DateTime|null $dateFinBattle La date de fin de la battle.
     * @param StatutBattle|null $statutBattle Le statut actuel de la battle.
     * @param string|null $emailCreateurBattle L'email du créateur de la battle.
     * @param string|null $emailParticipantBattle L'email du participant à la battle.
     */
    public function __construct(?int $idBattle = null, ?string $titreBattle = null, ?DateTime $dateDebutBattle = null, 
                                ?DateTime $dateFinBattle = null, ?StatutBattle $statutBattle = null, 
                                ?string $emailCreateurBattle = null, ?string $emailParticipantBattle = null)
    {
        $this->idBattle = $idBattle;
        $this->titreBattle = $titreBattle;
        $this->dateDebutBattle = $dateDebutBattle;
        $this->dateFinBattle = $dateFinBattle;
        $this->statutBattle = $statutBattle;
        $this->emailCreateurBattle = $emailCreateurBattle;
        $this->emailParticipantBattle = $emailParticipantBattle;
    }

    /**
     * Getter pour idBattle
     * @return int|null
     */
    public function getIdBattle(): ?int
    {
        return $this->idBattle;
    }
    /**
     * Setter pour idBattle
     * @param int|null $idBattle
     * @return void
     */
    public function setIdBattle(?int $idBattle): void
    {
        $this->idBattle = $idBattle;
    }

    /**
     * Getter pour titreBattle
     * @return string|null
     */
    public function getTitreBattle(): ?string
    {
        return $this->titreBattle;
    }
    /**
     * Setter pour titreBattle
     * @param string|null $titreBattle
     * @return void
     */
    public function setTitreBattle(?string $titreBattle): void
    {
        $this->titreBattle = $titreBattle;
    }

    /**
     * Getter pour dateDebutBattle
     * @return DateTime|null
     */
    public function getDateDebutBattle(): ?DateTime
    {
        return $this->dateDebutBattle;
    }
    /**
     * Setter pour dateDebutBattle
     * @param DateTime|null $dateDebutBattle
     * @return void
     */
    public function setDateDebutBattle(?DateTime $dateDebutBattle): void
    {
        $this->dateDebutBattle = $dateDebutBattle;
    }

    /**
     * Getter pour dateFinBattle
     * @return DateTime|null
     */
    public function getDateFinBattle(): ?DateTime
    {
        return $this->dateFinBattle;
    }
    /**
     * Setter pour dateFinBattle
     * @param DateTime|null $dateFinBattle
     * @return void
     */
    public function setDateFinBattle(?DateTime $dateFinBattle): void
    {
        $this->dateFinBattle = $dateFinBattle;
    }

    /**
     * Getter pour statutBattle
     * @return StatutBattle|null
     */
    public function getStatutBattle(): ?StatutBattle
    {
        return $this->statutBattle;
    }
    /**
     * Setter pour statutBattle
     * @param StatutBattle|null $statutBattle
     * @return void
     */
    public function setStatutBattle(?StatutBattle $statutBattle): void
    {
        $this->statutBattle = $statutBattle;
    }

    /**
     * Getter pour emailCreateurBattle
     * @return string|null
     */
    public function getEmailCreateurBattle(): ?string
    {
        return $this->emailCreateurBattle;
    }
    /**
     * Setter pour emailCreateurBattle
     * @param string|null $emailCreateurBattle
     * @return void
     */
    public function setEmailCreateurBattle(?string $emailCreateurBattle): void
    {
        $this->emailCreateurBattle = $emailCreateurBattle;
    }

    /**
     * Getter pour emailParticipantBattle
     * @return string|null
     */
    public function getEmailParticipantBattle(): ?string
    {
        return $this->emailParticipantBattle;
    }
    /**
     * Setter pour emailParticipantBattle
     * @param string|null $emailParticipantBattle
     * @return void
     */
    public function setEmailParticipantBattle(?string $emailParticipantBattle): void
    {
        $this->emailParticipantBattle = $emailParticipantBattle;
    }
}
?>