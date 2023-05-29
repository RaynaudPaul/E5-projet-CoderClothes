<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Définition de la classe MentionsLegalesController, qui étend AbstractController
 * et gère la route pour les mentions légales.
 */
class MentionsLegalesController extends AbstractController
{
    /**
     * Action pour afficher les mentions légales.
     *
     * @return Response La réponse HTTP
     */
    #[Route('/mentions/legales', name: 'app_mentions_legales')]
    public function index(): Response
    {
        // Rendre la vue 'mentions_legales/index.html.twig' en passant le nom du contrôleur comme variable
        return $this->render('mentions_legales/index.html.twig', [
            'controller_name' => 'MentionsLegalesController',
        ]);
    }
}
