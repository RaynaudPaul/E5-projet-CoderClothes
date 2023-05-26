<?php

namespace App\Controller\Admin;


use App\Entity\Coupons;
use App\Form\CouponFormType;
use App\Repository\CategoriesRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CouponsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/coupons', name: 'coupons_')]
class CouponsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CouponsRepository $couponsRepository): Response
    {
        $coupons = $couponsRepository->findBy([],['id' => 'asc']);

        //dd($categories);

        return $this->render('admin/coupons/index.html.twig',compact('coupons'));
    }

    #[Route('/add/', name: 'add')]
    public function add(Request $request,EntityManagerInterface $entityManager): Response
    {
        $coupon = new Coupons();

        $form = $this->createForm(CouponFormType::class,$coupon);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $coupon->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($coupon);
            $entityManager->flush();

            return $this->redirectToRoute('coupons_index');
        }

        return $this->render('admin/coupons/add.html.twig',[
            'form' => $form->createView(),
        ]);
    }

    #[Route('/remove/{id}', name: 'remove')]
    public function remove(EntityManagerInterface $entityManager,Coupons $coupons): Response
    {
        $entityManager->remove($coupons);

        $entityManager->flush();

        return $this->redirectToRoute('coupons_index');

    }
}