<?php

namespace App\Controller;

use App\Entity\Coupons;
use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Entity\Products;
use App\Entity\Users;
use App\Repository\CouponsRepository;
use App\Repository\OrdersRepository;
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

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(SessionInterface $session,TokenStorageInterface $tokenStorage): Response
    {
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

        }else{
            //return $this->redirectToRoute('app_login');
            $user = new Users();
        }

        if (!$session->has('order')) {

            $aOrder->setUsers($user);

            $session->set('order',$aOrder);
        }else{
            $aOrder = $session->get('order');

        }

        if (isset($aOrder) && $aOrder != array() && count($aOrder->getOrdersDetails()) > 0){
            $products = $aOrder->getOrdersDetails();

            //dd($products);
        }else{
            $aOrder = array();
        }

        //dd($aOrder);

        return $this->render('panier/index.html.twig', [
            'products'=>$products,'aOrder'=>$aOrder,
            'connected'=>$connected
        ]);
    }

    #[Route('/panier/add/{slug}', name: 'app_panier_add')]
    public function add(SessionInterface $session,TokenStorageInterface $tokenStorage,Products $product,Request $request): Response
    {
        //dd($products);

        $aOrder = new Orders();
        $products = array();
        $aOrderDetail = new OrdersDetails();
        $quantity = 0;

        $token = $tokenStorage->getToken();

        // Vérifier si un utilisateur est connecté
        if ($token && $token->getUser()) {
            // Récupérer l'objet User
            $user = $token->getUser();

        }else{
            $user = new Users();
        }

        if (!$session->has('order')) {

            $aOrder->setUsers($user);

            $session->set('order',$aOrder);
        }else{
            $aOrder = $session->get('order');
            //dd($aOrder);
        }

        //dd($aOrder);

        $quantity = $request->get("quantity");

        if($quantity < 0){
            $quantity = 0;
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
                $quantity+=$orderDetail->getQuantity();
                $aOrder->removeOrdersDetail($orderDetail);
            }
        }

        //dd($price);

        $aOrderDetail->setProducts($product);
        $aOrderDetail->setPrice($product->getPrice());
        $aOrderDetail->setQuantity($quantity);

        $aOrderDetail->setOrders($aOrder);

        //dd($aOrderDetail);


        if($aOrderDetail->getQuantity() !== 0 ){
            $aOrder->addOrdersDetail($aOrderDetail);
        }

        //dd($aOrder);

        $session->set('order',$aOrder);

        return $this->render('panier/add.html.twig', []);
    }

    #[Route('/panier/remove/{slug}', name: 'app_panier_remove')]
    public function remove(SessionInterface $session,TokenStorageInterface $tokenStorage,Products $product): Response
    {
        $aOrder = new Orders();
        $products = array();
        $aOrderDetail = new OrdersDetails();

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
                $aOrder->removeOrdersDetail($orderDetail);
            }
        }


        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/clear', name: 'app_panier_clear')]
    public function clear(SessionInterface $session): Response
    {
        $session->remove('order');

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/coupon', name: 'app_panier_coupon')]
    public function coupon(SessionInterface $session,TokenStorageInterface $tokenStorage,
                           Request $request,EntityManagerInterface $entityManager,
                           OrdersRepository $ordersRepository, CouponsRepository $couponsRepository): Response
    {
        $aOrder = array();
        $token = $tokenStorage->getToken();

        // Vérifier si un utilisateur est connecté
        if ($token && $token->getUser()) {
            // Récupérer l'objet User
            $user = $token->getUser();
        }else{
            return $this->redirectToRoute('app_login');
        }


        if (!$session->has('order')) {
            return $this->redirectToRoute('app_panier');
        }else{
            $aOrder = $session->get('order');
        }

        if (isset($aOrder) && $aOrder != array()){
            $products = $aOrder->getOrdersDetails();
        }else{
           return $this->redirectToRoute('app_panier');
        }

        $total = 0;

        foreach ($products as $orderDetail) {
            $total+= $orderDetail->getPrice()*$orderDetail->getQuantity();
        }

        if($total === 0){
            return $this->redirectToRoute('app_panier');
        }

        $coupon = $request->get("coupon");

        if($coupon === null){
            $coupon = "";
        }


        if($coupon !== null && $_SERVER['REQUEST_METHOD'] === 'POST'){
            //Vérification du coupon a faire !!!
            // ......

            $unCoupon = $couponsRepository->findOneBy(['code'=>$coupon]);

            //dd($coupon);

            if($coupon === ""){
                $aOrder->setCoupons(null);
            }
            else if($unCoupon === null){
                $error = true;

                return $this->render('panier/coupon.html.twig',compact('total','error','coupon'));
            }else{
                $couponReference = $entityManager->getReference(Coupons::class, $unCoupon->getId());

                $LesOrders = $ordersRepository->findBy(['coupons'=>$couponReference]);

                $orderCount = count($LesOrders);

                //dd($orderCount);

                $now = new DateTime();


                /*dump($unCoupon->getValidity());
                dd($now);*/

                $validity = $unCoupon->getValidity()->getTimestamp(); // Timestamp de la validité du coupon
                $now = $now->getTimestamp(); // Timestamp de la date et heure actuelles

                /*($validity);
                dd($now);*/

                if($orderCount < $unCoupon->getMaxUsage() && $validity > $now){
                    $aOrder->setCoupons($couponReference);
                }else{
                    $error = true;

                    return $this->render('panier/coupon.html.twig',compact('total','error','coupon'));
                }

            }

            //dd($unCoupon);


            //Enregistrement de la commande

            //dd($aOrder);

            //dd(new Orders());

            /*$unOrder = new Orders();

            $unOrder->setReference(123);

            $unOrder->setCreatedAt(new \DateTimeImmutable());

            $unOrder->setUsers("1");

            $entityManager->persist($unOrder);

            $entityManager->flush();

            $id = $ordersRepository->findMaxId()+1;*/

            //$aOrder->

            //dd($user);

            $userReference = $entityManager->getReference(Users::class, $user->getId());

            $aOrder->setUsers($userReference);

            $date = new \DateTime(); // Obtenir la date actuelle

            $dateString = $date->format('Ymd-His');

            $reference = $user->getId()."-".$dateString;

            $aOrder->setReference($reference);

            $aOrder->setCreatedAt(new \DateTimeImmutable());

            foreach ($aOrder->getOrdersDetails() as $aOrderDetail){
                //$entityManager->clear($aOrderDetail);
                $aOrderDetailRef = $entityManager->getReference(Products::class, $aOrderDetail->getProducts()->getId());
                $aOrderDetail->setProducts($aOrderDetailRef);

                $entityManager->persist($aOrderDetail);
            }

            //dd($aOrder);

            //$entityManager->clear($aOrder);
            $entityManager->persist($aOrder);


            $entityManager->flush();


            $session->remove('order');

            /*$entityManager->persist($aOrder);
            $entityManager->flush();*/

            return $this->render('panier/confCommande.html.twig',compact('reference'));
        }

        //dd($coupon);

        //dd($total);

        return $this->render('panier/coupon.html.twig',compact('total'));
    }
}
