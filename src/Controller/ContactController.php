<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Définition de la classe ContactController, qui étend AbstractController
 * et gère la route pour la page de contact.
 */
class ContactController extends AbstractController
{
    /**
     * Action pour afficher la page de contact.
     *
     * @return Response La réponse HTTP
     */
    #[Route('/contact', name: 'app_contact')]
    public function index(): Response
    {
        // Retourne la vue 'contact/index.html.twig' en passant le nom du contrôleur comme variable
        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }
}
