<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Form\ProfileFormType;
use App\Repository\OrdersRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Définition de la classe ProfileController, qui étend AbstractController
 * et gère les routes pour le profil.
 */
#[Route('/profil', name: 'app_profile_')]
class ProfileController extends AbstractController
{
    /**
     * Action pour afficher le profil de l'utilisateur.
     *
     * @param Request                   $request                La requête HTTP
     * @param UsersRepository           $usersRepository        Le dépôt des utilisateurs
     * @param TokenStorageInterface     $tokenStorage           L'interface de gestionnaire des jetons d'authentification
     * @param EntityManagerInterface   $entityManager         L'interface de gestionnaire d'entités
     * @return Response                 La réponse HTTP
     */
    #[Route('/', name: 'index')]
    public function index(Request $request, UsersRepository $usersRepository, TokenStorageInterface $tokenStorage, EntityManagerInterface $entityManager): Response
    {
        $token = $tokenStorage->getToken();

        // Vérifier si un utilisateur est connecté
        if ($token && $token->getUser()) {
            // Récupérer l'objet User
            $user = $token->getUser();

            $form = $this->createForm(ProfileFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();
                // Faire autre chose si nécessaire, comme envoyer un e-mail
            }

            return $this->render('profile/index.html.twig', [
                'user' => $user,
                'profileForm' => $form->createView(),
            ]);
        }

        return $this->redirectToRoute('app_login');
    }

    /**
     * Action pour afficher les commandes de l'utilisateur.
     *
     * @param TokenStorageInterface     $tokenStorage           L'interface de gestionnaire des jetons d'authentification
     * @return Response                 La réponse HTTP
     */
    #[Route('/commandes', name: 'orders')]
    public function orders(TokenStorageInterface $tokenStorage): Response
    {
        $token = $tokenStorage->getToken();

        // Vérifier si un utilisateur est connecté
        if ($token && $token->getUser()) {
            // Récupérer l'objet User
            $user = $token->getUser();
            $orders = $user->getOrders();

            return $this->render('profile/orders.html.twig', [
                'orders' => $orders
            ]);
        }

        return $this->redirectToRoute('app_login');
    }

    /**
     * Action pour afficher les détails d'une commande.
     *
     * @param TokenStorageInterface     $tokenStorage           L'interface de gestionnaire des jetons d'authentification
     * @param OrdersRepository          $ordersRepository       Le dépôt des commandes
     * @param int                       $orders_id              L'identifiant de la commande
     * @return Response                 La réponse HTTP
     */
    #[Route('/commandes/{orders_id}', name: 'orderDetail')]
    public function orderDetail(TokenStorageInterface $tokenStorage, OrdersRepository $ordersRepository, int $orders_id): Response
    {
        $token = $tokenStorage->getToken();
        $result = $ordersRepository->findBy(['reference' => $orders_id]);
        $products = array();
        $aOrder = array();

        // Vérifier si un utilisateur est connecté
        if ($token && $token->getUser()) {
            $user = $token->getUser();

            if ($result !== array()) {
                $aOrder = $result[0];
            }

            if (isset($aOrder) && $aOrder != array() && $user === $aOrder->getUsers()) {
                $products = $aOrder->getOrdersDetails();
            } else {
                $aOrder = array();
            }
        } else {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profile/orderDetail.html.twig', compact("products", "aOrder"));
    }
}

