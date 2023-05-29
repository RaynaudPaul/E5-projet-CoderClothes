<?php

namespace App\Controller\Admin;


use App\Entity\Categories;
use App\Form\CategoriesFormType;
use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Définition de la classe CategoriesController, qui étend AbstractController
 * et gère les routes pour les catégories de l'administration.
 */
#[Route('/admin/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{
    /**
     * Action pour afficher la liste des catégories.
     *
     * @param CategoriesRepository $categoriesRepository Le repository des catégories
     * @return Response La réponse HTTP
     */
    #[Route('/', name: 'index')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        // Récupérer les catégories triées par 'categoryOrder' de manière ascendante
        $categories = $categoriesRepository->findBy([], ['categoryOrder' => 'asc']);

        // Afficher les catégories (optionnel)
        //dd($categories);

        // Rendre la vue 'admin/categories/index.html.twig' en passant les catégories comme variable
        return $this->render('admin/categories/index.html.twig', compact('categories'));
    }

    /**
     * Action pour ajouter une nouvelle catégorie.
     *
     * @param Request                $request                La requête HTTP
     * @param CategoriesRepository   $categoriesRepository   Le repository des catégories
     * @param EntityManagerInterface $entityManager         L'interface de gestionnaire d'entité
     * @return Response La réponse HTTP
     */
    #[Route('/add/', name: 'add')]
    public function add(Request $request, CategoriesRepository $categoriesRepository, EntityManagerInterface $entityManager): Response
    {
        // Créer une nouvelle instance de Categories
        $categories = new Categories();

        // Récupérer toutes les catégories triées par 'categoryOrder' de manière ascendante
        $LesCategories = $categoriesRepository->findBy([], ['categoryOrder' => 'asc']);
        $existingOrders = [];
        $maxOrder = 0;

        // Parcourir les catégories existantes
        foreach ($LesCategories as $aCategorie) {
            // Afficher la catégorie (optionnel)
            //dd($aCategorie);

            $existingOrders[] = $aCategorie->getCategoryOrder();

            if ($aCategorie->getCategoryOrder() > $maxOrder) {
                $maxOrder = $aCategorie->getCategoryOrder();
            }
        }

        // Afficher les orders existants (optionnel)
        //dd($existingOrders);

        $choices = [];

        // Générer les choix pour l'ordre de catégorie
        for ($i = 1; $i <= $maxOrder + 1; $i++) {
            $choices[$i] = $i;
        }

        // Exclure les orders existants des choix disponibles
        $choices = array_diff($choices, $existingOrders);

        // Afficher les choix (optionnel)
        //dd($choices);

        // Créer le formulaire en utilisant CategoriesFormType et passer les catégories en tant que modèle
        $form = $this->createForm(CategoriesFormType::class, $categories)
            ->add('categoryOrder', ChoiceType::class, [
                'label' => 'Ordre de catégorie',
                'choices' => $choices,
            ]);
        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $categories->setSlug($categories->getName());

            // Persister la nouvelle catégorie
            $entityManager->persist($categories);
            $entityManager->flush();

            // Rediriger vers l'index des catégories
            return $this->redirectToRoute('categories_index');
        }

        // Rendre la vue 'admin/categories/add.html.twig' en passant le formulaire comme variable
        return $this->render('admin/categories/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Action pour supprimer une catégorie existante.
     *
     * @param Categories                $categories              La catégorie à supprimer
     * @param CategoriesRepository      $categoriesRepository     Le repository des catégories
     * @param ProductsRepository        $productsRepository       Le repository des produits
     * @param EntityManagerInterface    $entityManager           L'interface de gestionnaire d'entité
     * @return Response La réponse HTTP
     */
    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Categories $categories, CategoriesRepository $categoriesRepository, ProductsRepository $productsRepository, EntityManagerInterface $entityManager): Response
    {
        // Afficher la catégorie (optionnel)
        //dd($categories);

        // Récupérer les catégories qui ont la catégorie courante comme parent, triées par 'categoryOrder' de manière ascendante
        $LesCategories = $categoriesRepository->findBy(['parent' => $categories], ['categoryOrder' => 'asc']);

        // Afficher les catégories (optionnel)
        //dd($LesCategories);

        $message = "";

        if ($LesCategories === []) {
            // Récupérer les produits qui ont la catégorie courante, triés par 'id' de manière ascendante
            $LesProduits = $productsRepository->findBy(['Categories' => $categories], ['id' => 'asc']);

            // Afficher les produits (optionnel)
            //dd($LesProduits);

            if ($LesProduits === []) {
                // Supprimer la catégorie courante
                $entityManager->remove($categories);
                $entityManager->flush();

                // Rediriger vers l'index des catégories
                return $this->redirectToRoute('categories_index');
            }
            $message = "Impossible de supprimer cette catégorie car elle contient des produits";
        } else {
            $message = "Impossible de supprimer cette catégorie car elle est parente d'une autre";
        }

        // Récupérer toutes les catégories triées par 'categoryOrder' de manière ascendante
        $ListeCategories = $categoriesRepository->findBy([], ['categoryOrder' => 'asc']);

        // Rendre la vue 'admin/categories/index.html.twig' en passant les catégories et le message d'erreur comme variables
        return $this->render('admin/categories/index.html.twig', [
            'categories' => $ListeCategories,
            'error' => $message
        ]);
    }
}
