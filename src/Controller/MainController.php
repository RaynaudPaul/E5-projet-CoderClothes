<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\ImagesRepository;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Définition de la classe MainController, qui étend AbstractController
 * et gère la route pour la page d'accueil.
 */
class MainController extends AbstractController
{
    /**
     * Action pour afficher la page d'accueil.
     *
     * @param ProductsRepository    $productsRepository    Le repository des produits
     * @param CategoriesRepository  $categoriesRepository  Le repository des catégories
     * @param ImagesRepository      $imagesRepository      Le repository des images
     * @return Response La réponse HTTP
     */
    #[Route('/', name: 'main')]
    public function index(ProductsRepository $productsRepository, CategoriesRepository $categoriesRepository, ImagesRepository $imagesRepository): Response
    {
        // Récupérer toutes les catégories
        $categories = $categoriesRepository->findAll();

        foreach ($categories as $categorie) {
            // Récupérer les produits pour chaque catégorie
            $products = $productsRepository->findBy(['Categories' => $categorie]);

            foreach ($products as $product) {
                // Ajouter les produits à la catégorie
                $categorie->addProduct($product);
            }
        }

        foreach ($products as $product) {
            // Récupérer l'image associée au produit
            $image = $imagesRepository->findBy(['products' => $product]);

            // Ajouter l'image au produit
            $product->addImage($image[0]);
        }

        // Rendre la vue 'main/index.html.twig' en passant les catégories comme variable
        return $this->render('main/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /*
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }
    */
}

