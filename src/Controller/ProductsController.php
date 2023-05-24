<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Entity\Products;
use App\Entity\Users;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/products', name: 'app_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('products/index.html.twig', []);
    }

    #[Route('/{slug}', name: 'détails')]
    public function détails(SessionInterface $session,TokenStorageInterface $tokenStorage,Products $product): Response
    {
        $aOrder = new Orders();
        $products = array();
        $aOrderDetail = new OrdersDetails();
        $alreadyadded = false;


        $token = $tokenStorage->getToken();

        // Vérifier si un utilisateur est connecté
        if ($token && $token->getUser()) {
            // Récupérer l'objet User
            $user = $token->getUser();

        }else{
            $user = new Users();
        }

        if (!$session->has('order')) {
            return $this->redirectToRoute('app_panier');
        }else{
            $aOrder = $session->get('order');
            //dd($aOrder);
        }

        if (isset($aOrder) && $aOrder != array()){
            $products = $aOrder->getOrdersDetails();

            //dd($products);
        }else{
            $aOrder = array();
        }

        foreach ($products as $orderDetail) {
            //dd($orderDetail->getProducts());
            //dd($product);
            if ($orderDetail->getProducts()->getId() === $product->getId()) {
                $alreadyadded = true;
            }
        }
        return $this->render('products/details.html.twig',compact('product','alreadyadded'));
    }
}
