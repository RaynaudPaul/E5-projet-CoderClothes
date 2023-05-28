<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\ImagesRepository;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VêtementsController extends AbstractController
{
    #[Route('/vetements', name: 'app_vetements')]

    public function index(ProductsRepository $productsRepository, CategoriesRepository $categoriesRepository,ImagesRepository $imagesRepository): Response
    {
        $products = $productsRepository->findAll();
        $categories = $categoriesRepository->findAll();

        foreach ( $products as $product) {
            $image = $imagesRepository->findBy(['products'=>$product]);

            $product->addImage($image[0]);
        }


        return $this->render('vetements/index.html.twig', [
            'products'=> $products,
            'categories' => $categories,
        ]);
    }


}
