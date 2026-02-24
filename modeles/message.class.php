<?php

/** 
 * @file Classe Message
 * @brief Représente un message dans le système de messagerie
 */

class Message
{
    /**
     * @var int|null $idMessage L'identifiant unique du message.
     */
    private int|null $idMessage;

    /**
     * @var DateTime|null $dateEnvoi La date d'envoi du message.
     */
    private DateTime|null $dateEnvoi;

    /**
     * @var string|null $contenu Le contenu du message.
     */
    private string|null $contenu;

    /**
     * @var bool $estLu Indique si le message a ete lu
     */
    private bool $estLu = false;

    /**
     * @var Utilisateur|null $emailExpediteur L'email de l'expéditeur du message.
     */
    private Utilisateur|null $emailExpediteur;

    /**
     * @var Utilisateur|null $emailDestinataire L'email du destinataire du message.
     */
    private Utilisateur|null $emailDestinataire;

    /**
     * Constructeur de la classe Message.
     * @param int|null $idMessage L'identifiant unique.
     * @param string|null $contenu Le contenu du message.
     * @param DateTime|null $dateEnvoi La date d'envoi du message.
     * @param bool $estLu Indique si le message a ete lu
     * @param Utilisateur|null $emailExpediteur L'email de l'expéditeur du message.
     * @param Utilisateur|null $emailDestinataire L'email du destinataire du message.
     */
    public function __construct(?int $idMessage = null, ?string $contenu = null, ?DateTime $dateEnvoi = null, bool $estLu = false,?Utilisateur $emailExpediteur = null, ?Utilisateur $emailDestinataire = null)
    {
        $this->idMessage = $idMessage;
        $this->contenu = $contenu;
        $this->dateEnvoi = $dateEnvoi;
        $this->estLu = $estLu;
        $this->emailExpediteur = $emailExpediteur;
        $this->emailDestinataire = $emailDestinataire;
    }

    /**
     * Get $idMessage L'identifiant unique du message.
     *
     * @return  int|null
     */ 
    public function getIdMessage()
    {
        return $this->idMessage;
    }

    /**
     * Set $idMessage L'identifiant unique du message.
     *
     * @param  int|null  $idMessage  $idMessage L'identifiant unique du message.
     *
     * @return  self
     */ 
    public function setIdMessage($idMessage)
    {
        $this->idMessage = $idMessage;

        return $this;
    }

    /**
     * Get the value of dateEnvoi
     */ 
    public function getDateEnvoi()
    {
        return $this->dateEnvoi;
    }

    /**
     * Set the value of dateEnvoi
     *
     * @return  self
     */ 
    public function setDateEnvoi($dateEnvoi)
    {
        $this->dateEnvoi = $dateEnvoi;

        return $this;
    }

    /**
     * Get $contenu Le contenu du message.
     *
     * @return  string|null
     */ 
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set $contenu Le contenu du message.
     *
     * @param  string|null  $contenu  $contenu Le contenu du message.
     *
     * @return  self
     */ 
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get the value of estLu
     */ 
    public function getEstLu()
    {
        return $this->estLu;
    }

    /**
     * Set the value of estLu
     *
     * @return  self
     */ 
    public function setEstLu($estLu)
    {
        $this->estLu = $estLu;

        return $this;
    }

    

    /**
     * Get the value of emailExpediteur
     */ 
    public function getEmailExpediteur()
    {
        return $this->emailExpediteur;
    }

    /**
     * Set the value of emailExpediteur
     *
     * @return  self
     */ 
    public function setEmailExpediteur($emailExpediteur)
    {
        $this->emailExpediteur = $emailExpediteur;

        return $this;
    }

    /**
     * Get $emailDestinataire L'email du destinataire du message.
     *
     * @return  Utilisateur|null
     */ 
    public function getEmailDestinataire()
    {
        return $this->emailDestinataire;
    }

    /**
     * Set $emailDestinataire L'email du destinataire du message.
     *
     * @param  Utilisateur|null  $emailDestinataire  $emailDestinataire L'email du destinataire du message.
     *
     * @return  self
     */ 
    public function setEmailDestinataire($emailDestinataire)
    {
        $this->emailDestinataire = $emailDestinataire;

        return $this;
    }
}

?>