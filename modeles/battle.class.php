<?php

enum StatutBattle: string {
    case En_attente = 'en_attente';
    case En_cours = 'en_cours';
    case Terminee = 'terminee';
}

class Battle {
    private int $idBattle;
    private string $titreBattle;
    private DateTime $dateDebutBattle;
    private DateTime $dateFinBattle;
    private StatutBattle $statutBattle;
    private string $emailCreateurBattle;
    private string|null $emailParticipantBattle;

    public function __construct(int $idBattle, string $titreBattle, DateTime $dateDebutBattle,
                                DateTime $dateFinBattle, StatutBattle $statutBattle,
                                string $emailCreateurBattle, ?string $emailParticipantBattle)
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
     * Get the value of idBattle
     */
    public function getIdBattle(): int
    {
        return $this->idBattle;
    }
    /**
     * Set the value of idBattle
     */
    public function setIdBattle(int $idBattle): void
    {
        $this->idBattle = $idBattle;
    }

    /**
     * Get the value of titreBattle
     */
    public function getTitreBattle(): string
    {
        return $this->titreBattle;
    }
    /**
     * Set the value of titreBattle
     */
    public function setTitreBattle(string $titreBattle): void
    {
        $this->titreBattle = $titreBattle;
    }

    /**
     * Get the value of dateDebutBattle
     */
    public function getDateDebutBattle(): DateTime
    {
        return $this->dateDebutBattle;
    }
    /**
     * Set the value of dateDebutBattle
     */
    public function setDateDebutBattle(DateTime $dateDebutBattle): void
    {
        $this->dateDebutBattle = $dateDebutBattle;
    }

    /**
     * Get the value of dateFinBattle
     */
    public function getDateFinBattle(): DateTime
    {
        return $this->dateFinBattle;
    }
    /**
     * Set the value of dateFinBattle
     */
    public function setDateFinBattle(DateTime $dateFinBattle): void
    {
        $this->dateFinBattle = $dateFinBattle;
    }

    /**
     * Get the value of statutBattle
     */
    public function getStatutBattle(): StatutBattle
    {
        return $this->statutBattle;
    }
    /**
     * Set the value of statutBattle
     */
    public function setStatutBattle(StatutBattle $statutBattle): void
    {
        $this->statutBattle = $statutBattle;
    }

    /**
     * Get the value of emailCreateurBattle
     */
    public function getEmailCreateurBattle(): string
    {
        return $this->emailCreateurBattle;
    }
    /**
     * Set the value of emailCreateurBattle
     */
    public function setEmailCreateurBattle(string $emailCreateurBattle): void
    {
        $this->emailCreateurBattle = $emailCreateurBattle;
    }

    /**
     * Get the value of emailParticipantBattle
     */
    public function getEmailParticipantBattle(): ?string
    {
        return $this->emailParticipantBattle;
    }
    /**
     * Set the value of emailParticipantBattle
     */
    public function setEmailParticipantBattle(?string $emailParticipantBattle): void
    {
        $this->emailParticipantBattle = $emailParticipantBattle;
    }
}
?>