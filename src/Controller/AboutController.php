<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Définition de la classe AboutController, qui étend AbstractController
 * et gère la route pour la page a propos.
 */
class AboutController extends AbstractController
{
    /**
     * Action pour afficher la page "À propos".
     *
     * @return Response La réponse HTTP
     */
    #[Route('/about', name: 'app_about')]
    public function index(): Response
    {
        // Retourne la vue 'about/index.html.twig' en passant le nom du contrôleur comme variable

        return $this->render('about/index.html.twig', [
            'controller_name' => 'AboutController',
        ]);
    }
}
