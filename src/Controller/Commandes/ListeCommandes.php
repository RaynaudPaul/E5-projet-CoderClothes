<?php

namespace App\Controller\Commandes;


use App\Entity\Users;
use App\Form\ProfileFormType;
use App\Repository\OrdersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Définition de la classe ListeCommandes, qui étend AbstractController
 * et gère les routes pour les commandes.
 */
#[Route('/commandes', name: 'commandes_')]
class ListeCommandes extends AbstractController
{
    /**
     * Action pour afficher la liste des commandes.
     *
     * @param OrdersRepository $ordersRepository Le repository des commandes
     * @return Response La réponse HTTP
     */
    #[Route('/', name: 'index')]
    public function index(OrdersRepository $ordersRepository): Response
    {
        // Vérifier les autorisations des rôles 'ROLE_PRODUCT_ADMIN' et 'ROLE_ORDER'
        if (!$this->isGranted('ROLE_PRODUCT_ADMIN') && !$this->isGranted('ROLE_ORDER')) {
            throw $this->createAccessDeniedException('not allowed');
        }

        // Récupérer les commandes triées par 'id' de manière ascendante
        $orders = $ordersRepository->findBy([], ['id' => 'asc']);

        // Rendre la vue 'commandes/listeCommande.html.twig' en passant les commandes comme variable
        return $this->render('commandes/listeCommande.html.twig', compact('orders'));
    }

    /**
     * Action pour afficher les informations d'un utilisateur.
     *
     * @param Users $users L'utilisateur
     * @return Response La réponse HTTP
     */
    #[Route('/infoUtil/{id}', name: 'userInfo')]
    public function userInfo(Users $users): Response
    {
        // Vérifier l'autorisation du rôle 'ROLE_PRODUCT_ADMIN'
        $this->denyAccessUnlessGranted('ROLE_PRODUCT_ADMIN');

        // Créer le formulaire en utilisant ProfileFormType et passer l'utilisateur en tant que modèle
        $form = $this->createForm(ProfileFormType::class, $users);

        // Rendre la vue 'commandes/userInfo.html.twig' en passant le formulaire comme variable
        return $this->render('commandes/userInfo.html.twig', ['profileForm' => $form->createView()]);
    }
}
