<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VêtementsController extends AbstractController
{
    #[Route('/vetements', name: 'app_vetements')]

    public function index(ProductsRepository $productsRepository, CategoriesRepository $categoriesRepository): Response
    {
        $products = $productsRepository->findAll();
        $categories = $categoriesRepository->findAll();


        return $this->render('vetements/index.html.twig', [
            'products'=> $products,
            'categories' => $categories,
        ]);
    }


}
