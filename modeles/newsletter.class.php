<?php

class Newsletter
{
    private ?string $email = null;
    private ?DateTime $dateInscription = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getDateInscription(): ?DateTime
    {
        return $this->dateInscription;
    }

    public function setDateInscription(?DateTime $dateInscription): void
    {
        $this->dateInscription = $dateInscription;
    }
}
