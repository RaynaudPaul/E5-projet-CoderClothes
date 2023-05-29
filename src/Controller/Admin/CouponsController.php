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

/**
 * Définition de la classe CouponsController, qui étend AbstractController
 * et gère les routes pour les coupons de l'administration.
 */
#[Route('/admin/coupons', name: 'coupons_')]
class CouponsController extends AbstractController
{
    /**
     * Action pour afficher la liste des coupons.
     *
     * @param CouponsRepository $couponsRepository Le repository des coupons
     * @return Response La réponse HTTP
     */
    #[Route('/', name: 'index')]
    public function index(CouponsRepository $couponsRepository): Response
    {
        // Récupérer les coupons triés par 'id' de manière ascendante
        $coupons = $couponsRepository->findBy([], ['id' => 'asc']);

        // Afficher les coupons (optionnel)
        //dd($coupons);

        // Rendre la vue 'admin/coupons/index.html.twig' en passant les coupons comme variable
        return $this->render('admin/coupons/index.html.twig', compact('coupons'));
    }

    /**
     * Action pour ajouter un nouveau coupon.
     *
     * @param Request                $request              La requête HTTP
     * @param EntityManagerInterface $entityManager       L'interface de gestionnaire d'entité
     * @return Response La réponse HTTP
     */
    #[Route('/add/', name: 'add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer une nouvelle instance de Coupons
        $coupon = new Coupons();

        // Créer le formulaire en utilisant CouponFormType et passer le coupon en tant que modèle
        $form = $this->createForm(CouponFormType::class, $coupon);
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $coupon->setCreatedAt(new \DateTimeImmutable());

            // Persister le nouveau coupon
            $entityManager->persist($coupon);
            $entityManager->flush();

            // Rediriger vers l'index des coupons
            return $this->redirectToRoute('coupons_index');
        }

        // Rendre la vue 'admin/coupons/add.html.twig' en passant le formulaire comme variable
        return $this->render('admin/coupons/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Action pour supprimer un coupon existant.
     *
     * @param EntityManagerInterface $entityManager L'interface de gestionnaire d'entité
     * @param Coupons                $coupons        Le coupon à supprimer
     * @return Response La réponse HTTP
     */
    #[Route('/remove/{id}', name: 'remove')]
    public function remove(EntityManagerInterface $entityManager, Coupons $coupons): Response
    {
        // Supprimer le coupon
        $entityManager->remove($coupons);
        $entityManager->flush();

        // Rediriger vers l'index des coupons
        return $this->redirectToRoute('coupons_index');
    }
}
