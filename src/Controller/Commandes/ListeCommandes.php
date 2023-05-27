<?php

namespace App\Controller\Commandes;


use App\Entity\Users;
use App\Form\ProfileFormType;
use App\Repository\OrdersRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/commandes', name: 'commandes_')]
class ListeCommandes extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(OrdersRepository $ordersRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PRODUCT_ADMIN');

        $orders = $ordersRepository->findBy([],['id' => 'asc']);

        return $this->render('commandes/listeCommande.html.twig',
            compact('orders'));
    }

    #[Route('/infoUtil/{id}', name: 'userInfo')]
    public function userInfo(Users $users): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PRODUCT_ADMIN');

        $form = $this->createForm(ProfileFormType::class, $users);

        return $this->render('commandes/userInfo.html.twig',
            ['profileForm' => $form->createView()]);
    }
}