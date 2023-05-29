<?php

namespace App\Controller\Admin;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Définition de la classe AdminController, qui étend AbstractController
 * et gère les routes pour l'administration.
 */
#[Route('/admin', name: 'admin_')]
class AdminController extends AbstractController
{
    /**
     * Action pour afficher la page d'accueil de l'administration.
     *
     * @return Response La réponse HTTP
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        // Retourne la vue 'admin/index.html.twig'
        return $this->render('admin/index.html.twig');
    }
}
