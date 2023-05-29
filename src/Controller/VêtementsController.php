<?php

namespace App\Controller;

use App\Repository\CategoriesRepository;
use App\Repository\ImagesRepository;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Contrôleur pour la gestion des vêtements.
 */
class VêtementsController extends AbstractController
{
    /**
     * Action pour afficher la page des vêtements.
     *
     * @param ProductsRepository     $productsRepository     Le dépôt des produits
     * @param CategoriesRepository   $categoriesRepository   Le dépôt des catégories
     * @param ImagesRepository       $imagesRepository       Le dépôt des images
     * @return Response              La réponse HTTP
     */
    #[Route('/vetements', name: 'app_vetements')]
    public function index(ProductsRepository $productsRepository, CategoriesRepository $categoriesRepository, ImagesRepository $imagesRepository): Response
    {
        // Récupérer tous les produits
        $products = $productsRepository->findAll();

        // Récupérer toutes les catégories
        $categories = $categoriesRepository->findAll();

        // Parcourir les produits
        foreach ($products as $product) {
            // Récupérer l'image associée au produit
            $image = $imagesRepository->findBy(['products' => $product]);

            // Ajouter l'image au produit
            $product->addImage($image[0]);
        }

        // Rendre la vue "vetements/index.html.twig"
        return $this->render('vetements/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
