<?php

namespace App\Controller;

use App\Entity\Coupons;
use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Entity\Products;
use App\Entity\Users;
use App\Repository\CouponsRepository;
use App\Repository\OrdersRepository;
use App\Repository\ProductsRepository;
use Couchbase\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Définition de la classe PanierController, qui étend AbstractController
 * et gère la route pour le panier.
 */
class PanierController extends AbstractController
{
    /**
     * Action pour afficher les mentions légales.
     *
     * @param SessionInterface         $session             L'interface de gestionnaire de la session
     * @param TokenStorageInterface    $tokenStorage        L'interface du gestionnaire de token
     * @return Response La réponse HTTP
     */
    #[Route('/panier', name: 'app_panier')]
    public function index(SessionInterface $session, TokenStorageInterface $tokenStorage): Response
    {
        // Initialisation des variables
        $user = array();
        $aOrder = new Orders();
        $products = array();
        $connected = false;

        $token = $tokenStorage->getToken();

        // Vérifier si un utilisateur est connecté
        if ($token && $token->getUser()) {
            // Récupérer l'objet User
            $user = $token->getUser();
            $connected = true;
        } else {
            // Rediriger vers la page de connexion
            // return $this->redirectToRoute('app_login');
            $user = new Users();
        }

        // Vérifier si la session contient une commande
        if (!$session->has('order')) {
            // Définir l'utilisateur de la commande
            $aOrder->setUsers($user);
            $session->set('order', $aOrder);
        } else {
            $aOrder = $session->get('order');
        }

        // Vérifier s'il y a des produits dans la commande
        if (isset($aOrder) && $aOrder != array() && count($aOrder->getOrdersDetails()) > 0) {
            $products = $aOrder->getOrdersDetails();
        } else {
            $aOrder = array();
        }

        return $this->render('panier/index.html.twig', [
            'products' => $products,
            'aOrder' => $aOrder,
            'connected' => $connected
        ]);
    }


    /**
     * Action pour ajouter un produit au panier.
     *
     * @param SessionInterface         $session             L'interface de gestionnaire de la session
     * @param TokenStorageInterface    $tokenStorage        L'interface de gestionnaire des jetons d'authentification
     * @param Products                 $product             Le produit à ajouter au panier
     * @param Request                  $request             La requête HTTP
     * @return Response                La réponse HTTP
     */
    #[Route('/panier/add/{slug}', name: 'app_panier_add')]
    public function add(SessionInterface $session, TokenStorageInterface $tokenStorage, Products $product, Request $request): Response
    {
        // Initialisation des variables
        $aOrder = new Orders();
        $products = array();
        $aOrderDetail = new OrdersDetails();
        $quantity = 0;

        $token = $tokenStorage->getToken();

        // Vérifier si un utilisateur est connecté
        if ($token && $token->getUser()) {
            // Récupérer l'objet User connecté
            $user = $token->getUser();
        } else {
            // Créer un nouvel utilisateur pour le panier
            $user = new Users();
        }

        // Vérifier si la commande existe dans la session
        if (!$session->has('order')) {
            // Définir l'utilisateur de la commande
            $aOrder->setUsers($user);
            $session->set('order', $aOrder);
        } else {
            // Récupérer la commande depuis la session
            $aOrder = $session->get('order');
        }

        $quantity = $request->get("quantity");

        // Vérifier si la quantité est inférieure à zéro
        if ($quantity < 0) {
            $quantity = 0;
        }

        // Vérifier s'il y a déjà des produits dans la commande
        if (isset($aOrder) && $aOrder != array()) {
            $products = $aOrder->getOrdersDetails();
        } else {
            $aOrder = array();
        }

        // Parcourir les produits dans la commande
        foreach ($products as $orderDetail) {
            if ($orderDetail->getProducts()->getId() === $product->getId()) {
                // Ajouter la quantité spécifiée à la quantité existante du produit
                $quantity += $orderDetail->getQuantity();
                $aOrder->removeOrdersDetail($orderDetail);
            }
        }

        // Définir les informations du produit à ajouter
        $aOrderDetail->setProducts($product);
        $aOrderDetail->setPrice($product->getPrice());
        $aOrderDetail->setQuantity($quantity);
        $aOrderDetail->setOrders($aOrder);

        // Ajouter le détail de commande au panier
        if ($aOrderDetail->getQuantity() !== 0) {
            $aOrder->addOrdersDetail($aOrderDetail);
        }

        // Mettre à jour la commande dans la session
        $session->set('order', $aOrder);

        // Rendre la vue "panier/add.html.twig"
        return $this->render('panier/add.html.twig', []);
    }


    /**
     * Action pour supprimer un produit du panier.
     *
     * @param SessionInterface         $session             L'interface de gestionnaire de la session
     * @param TokenStorageInterface    $tokenStorage        L'interface de gestionnaire des jetons d'authentification
     * @param Products                 $product             Le produit à supprimer du panier
     * @return Response                La réponse HTTP
     */
    #[Route('/panier/remove/{slug}', name: 'app_panier_remove')]
    public function remove(SessionInterface $session, TokenStorageInterface $tokenStorage, Products $product): Response
    {
        $aOrder = new Orders();
        $products = array();
        $aOrderDetail = new OrdersDetails();

        $token = $tokenStorage->getToken();

        // Vérifier si un utilisateur est connecté
        if ($token && $token->getUser()) {
            // Récupérer l'objet User
            $user = $token->getUser();
        } else {
            $user = new Users();
        }

        // Vérifier si la commande existe dans la session
        if (!$session->has('order')) {
            return $this->redirectToRoute('app_panier');
        } else {
            $aOrder = $session->get('order');
        }

        // Vérifier s'il y a des produits dans la commande
        if (isset($aOrder) && $aOrder != array()) {
            $products = $aOrder->getOrdersDetails();
        } else {
            $aOrder = array();
        }

        // Parcourir les produits dans la commande
        foreach ($products as $orderDetail) {
            if ($orderDetail->getProducts()->getId() === $product->getId()) {
                // Supprimer le détail de commande correspondant au produit
                $aOrder->removeOrdersDetail($orderDetail);
            }
        }

        // Rediriger vers la page du panier
        return $this->redirectToRoute('app_panier');
    }


    /**
     * Action pour vider le panier.
     *
     * @param SessionInterface $session L'interface de gestionnaire de la session
     * @return Response La réponse HTTP
     */
    #[Route('/panier/clear', name: 'app_panier_clear')]
    public function clear(SessionInterface $session): Response
    {
        $session->remove('order');

        // Rediriger vers la page du panier
        return $this->redirectToRoute('app_panier');
    }


    /**
     * Action pour appliquer un coupon au panier.
     *
     * @param SessionInterface         $session             L'interface de gestionnaire de la session
     * @param TokenStorageInterface    $tokenStorage        L'interface de stockage du jeton d'authentification
     * @param Request                  $request             La requête HTTP
     * @param EntityManagerInterface  $entityManager       L'interface du gestionnaire d'entités
     * @param OrdersRepository         $ordersRepository    Le repository des commandes
     * @param CouponsRepository        $couponsRepository   Le repository des coupons
     * @param ProductsRepository       $productsRepository  Le repository des produits
     * @return Response La réponse HTTP
     */
    #[Route('/panier/coupon', name: 'app_panier_coupon')]
    public function coupon(
        SessionInterface $session,
        TokenStorageInterface $tokenStorage,
        Request $request,
        EntityManagerInterface $entityManager,
        OrdersRepository $ordersRepository,
        CouponsRepository $couponsRepository,
        ProductsRepository $productsRepository
    ): Response {
        $aOrder = array(); // Initialisation de la variable $aOrder

        $token = $tokenStorage->getToken(); // Récupération du jeton d'authentification

        // Vérifier si un utilisateur est connecté
        if ($token && $token->getUser()) {
            // Récupérer l'objet User
            $user = $token->getUser();
        } else {
            return $this->redirectToRoute('app_login'); // Redirection vers la page de connexion si l'utilisateur n'est pas connecté
        }

        if (!$session->has('order')) {
            return $this->redirectToRoute('app_panier'); // Redirection vers la page du panier si le panier n'existe pas en session
        } else {
            $aOrder = $session->get('order'); // Récupération du panier depuis la session
        }

        if (isset($aOrder) && $aOrder != array()) {
            $products = $aOrder->getOrdersDetails(); // Récupération des détails de commande (produits) du panier
        } else {
            return $this->redirectToRoute('app_panier'); // Redirection vers la page du panier si le panier est vide
        }

        $total = 0; // Initialisation du total du panier

        // Calcul du total du panier en additionnant les prix des produits
        foreach ($products as $orderDetail) {
            $total += $orderDetail->getPrice() * $orderDetail->getQuantity();
        }

        if ($total === 0) {
            return $this->redirectToRoute('app_panier'); // Redirection vers la page du panier si le total est égal à 0
        }

        $coupon = $request->get("coupon"); // Récupération du code de coupon depuis la requête

        if ($coupon === null) {
            $coupon = "";
        }

        if ($coupon !== null && $_SERVER['REQUEST_METHOD'] === 'POST') {
            //Vérification du coupon à faire !!!
            // ......

            $unCoupon = $couponsRepository->findOneBy(['code' => $coupon]); // Recherche du coupon correspondant au code

            // Si aucun coupon n'est saisi
            if ($coupon === "") {
                $aOrder->setCoupons(null); // Suppression du coupon du panier
            } else if ($unCoupon === null) { // Si le coupon n'existe pas dans la base de données
                $error = true; // Indicateur d'erreur

                return $this->render('panier/coupon.html.twig', compact('total', 'error', 'coupon')); // Affichage de la page du panier avec l'erreur
            } else {
                $couponReference = $entityManager->getReference(Coupons::class, $unCoupon->getId()); // Référence du coupon

                $LesOrders = $ordersRepository->findBy(['coupons' => $couponReference]); // Recherche des commandes utilisant le même coupon

                $orderCount = count($LesOrders); // Comptage du nombre de commandes associées à ce coupon

                $now = new DateTime(); // Obtenir la date et l'heure actuelles

                $validity = $unCoupon->getValidity()->getTimestamp(); // Timestamp de la validité du coupon
                $now = $now->getTimestamp(); // Timestamp de la date et heure actuelles

                // Vérification du nombre d'utilisations du coupon et de sa validité
                if ($orderCount < $unCoupon->getMaxUsage() && $validity > $now) {
                    $aOrder->setCoupons($couponReference); // Appliquer le coupon au panier
                } else {
                    $error = true; // Indicateur d'erreur

                    $couponReference->setIsValid(false); // Marquer le coupon comme invalide

                    $entityManager->persist($couponReference); // Persister les modifications
                    $entityManager->flush();

                    return $this->render('panier/coupon.html.twig', compact('total', 'error', 'coupon')); // Affichage de la page du panier avec l'erreur
                }
            }

            //Enregistrement de la commande

            $userReference = $entityManager->getReference(Users::class, $user->getId()); // Référence de l'utilisateur

            $aOrder->setUsers($userReference); // Association de l'utilisateur à la commande

            $date = new \DateTime(); // Obtenir la date actuelle

            $dateString = $date->format('Ymd-His'); // Formatage de la date en chaîne de caractères

            $reference = $user->getId() . "-" . $dateString; // Génération de la référence de commande
            $aOrder->setReference($reference); // Attribution de la référence à la commande

            $aOrder->setCreatedAt(new \DateTimeImmutable()); // Définition de la date de création de la commande

            foreach ($aOrder->getOrdersDetails() as $aOrderDetail) {
                //$entityManager->clear($aOrderDetail);

                $aOrderDetailRef = $entityManager->getReference(Products::class, $aOrderDetail->getProducts()->getId()); // Référence du produit
                $aOrderDetail->setProducts($aOrderDetailRef); // Association du produit aux détails de commande

                $entityManager->persist($aOrderDetail); // Persister les détails de commande

                $id = $aOrderDetail->getProducts()->getId();

                $product = $productsRepository->find($id); // Récupération du produit correspondant

                $product->setStock($product->getStock() - $aOrderDetail->getQuantity()); // Mise à jour du stock du produit

                $entityManager->persist($product); // Persister les modifications du produit
            }

            $entityManager->persist($aOrder); // Persister la commande

            $entityManager->flush(); // Exécuter les opérations de persistance

            $session->remove('order'); // Suppression du panier de la session

            return $this->render('panier/confCommande.html.twig', compact('reference')); // Affichage de la page de confirmation de commande
        }

        return $this->render('panier/coupon.html.twig', compact('total')); // Affichage de la page du panier avec le total
    }

}
