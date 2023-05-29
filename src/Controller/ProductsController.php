<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Entity\Products;
use App\Entity\Users;
use App\Repository\ImagesRepository;
use App\Repository\ProductsRepository;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * Définition de la classe ProductsController, qui étend AbstractController
 * et gère les routes pour les produits.
 */
#[Route('/products', name: 'app_products_')]
class ProductsController extends AbstractController
{
    /**
     * Action pour afficher la page d'accueil des produits.
     *
     * @return Response La réponse HTTP
     */
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_vetements');
    }

    /**
     * Action pour afficher les détails d'un produit.
     *
     * @param SessionInterface         $session             L'interface de gestionnaire de la session
     * @param TokenStorageInterface    $tokenStorage        L'interface de gestionnaire des jetons d'authentification
     * @param ProductsRepository      $productsRepository   Le repository des produits
     * @param ImagesRepository         $imagesRepository    Le repository des images
     * @param Request                  $request             La requête HTTP
     * @return Response                La réponse HTTP
     */
    #[Route('/{slug}', name: 'détails')]
    public function détails(SessionInterface $session, TokenStorageInterface $tokenStorage,
                            ProductsRepository $productsRepository, ImagesRepository $imagesRepository, Request $request): Response
    {
        $aOrder = new Orders();
        $products = array();
        $aOrderDetail = new OrdersDetails();
        $alreadyadded = false;

        $slug = $request->attributes->get('slug');

        $product = $productsRepository->findOneBy(['slug' => $slug]);

        // Vérifier si le produit existe
        if ($product === null) {
            return $this->render('products/notFound.html.twig');
        }

        // Récupérer l'image du produit
        $image = $imagesRepository->findBy(['products' => $product]);
        $product->addImage($image[0]);

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
            $aOrder->setUsers($user);
            $session->set('order', $aOrder);
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
                $alreadyadded = true;
            }
        }

        return $this->render('products/details.html.twig', compact('product', 'alreadyadded'));
    }
}

