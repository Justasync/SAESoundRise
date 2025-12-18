<?php
/**
 * @file modeles/newsletter.class.php
 * @brief Classe représentant un abonnement à la newsletter
 */

class Newsletter
{
    /**
     * @var string|null $email L'adresse email de l'abonné.
     */
    private ?string $email = null;

    /**
     * @var DateTime|null $dateInscription La date d'inscription à la newsletter.
     */
    private ?DateTime $dateInscription = null;

    /**
     * Getter pour l'email de l'abonné
     * @return string|null L'adresse email.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Setter pour l'email de l'abonné
     * @param string|null $email L'adresse email.
     * @return void
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * Getter pour la date d'inscription
     * @return DateTime|null La date d'inscription.
     */
    public function getDateInscription(): ?DateTime
    {
        return $this->dateInscription;
    }

    /**
     * Setter pour la date d'inscription
     * @param DateTime|null $dateInscription La date d'inscription.
     * @return void
     */
    public function setDateInscription(?DateTime $dateInscription): void
    {
        $this->dateInscription = $dateInscription;
    }
}

