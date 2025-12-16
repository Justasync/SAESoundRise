<?php
/**
 * @file controller/controller_conditionsGenerales.class.php
 * @brief Controller pour la page des Conditions Générales
 */
class ControllerConditionsGenerales extends Controller
{
    public function __construct(\Twig\Environment $twig, \Twig\Loader\FilesystemLoader $loader)
    {
        parent::__construct($loader, $twig);
    }

    public function afficher()
    {
        $template = $this->getTwig()->load('conditions_generales.html.twig');
        echo $template->render(array(
            "page" => [
                'title' => "Conditions Générales",
                'name' => "conditions_generales",
                'description' => "Conditions Générales de Paaxio"
            ],
        ));
    }
}
