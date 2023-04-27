<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VêtementsController extends AbstractController
{
    #[Route('/v/tements', name: 'app_v_tements')]

    public function index(ProductsRepository $productsRepository, CategoriesRepository $categoriesRepository): Response
    {
        $products = $productsRepository->findAll();
        $categories = $categoriesRepository->findAll();


        return $this->render('vêtements/index.html.twig', [
            'products'=> $products,
            'categories' => $categories,
        ]);
    }


}
