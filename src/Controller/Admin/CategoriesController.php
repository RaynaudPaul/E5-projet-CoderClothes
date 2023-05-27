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

#[Route('/admin/categories', name: 'categories_')]
class CategoriesController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findBy([],['categoryOrder' => 'asc']);

        //dd($categories);

        return $this->render('admin/categories/index.html.twig',compact('categories'));
    }

    #[Route('/add/', name: 'add')]
    public function add(Request $request,CategoriesRepository $categoriesRepository,
                        EntityManagerInterface $entityManager): Response
    {
        $categories = new Categories();

        $LesCategories = $categoriesRepository->findBy([],['categoryOrder' => 'asc']);
        $existingOrders = [];

        $maxOrder = 0;

        foreach ($LesCategories as $aCategorie) {
            //dd($aCategorie);

            $existingOrders[] = $aCategorie->getCategoryOrder();

            if($aCategorie->getCategoryOrder() > $maxOrder){
                $maxOrder = $aCategorie->getCategoryOrder();
            }
        }

        //dd($existingOrders);

        //dd($LesCategories);

        $choices = [];

        for ($i = 1; $i <= $maxOrder+1; $i++) {
            $choices[$i] = $i;
        }

        $choices = array_diff($choices, $existingOrders);

        //dd($choices);

        $form = $this->createForm(CategoriesFormType::class,$categories)
            ->add('categoryOrder', ChoiceType::class, [
                'label' => 'Ordre de catégorie',
                'choices' => $choices,
            ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $categories->setSlug($categories->getName());

            $entityManager->persist($categories);
            $entityManager->flush();

            return $this->redirectToRoute('categories_index');
        }

        return $this->render('admin/categories/add.html.twig',[
            'form' => $form->createView(),
        ]);
    }


    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Categories $categories,CategoriesRepository $categoriesRepository,
                           ProductsRepository $productsRepository,
                           EntityManagerInterface $entityManager): Response
    {
        //dd($categories);

        $LesCategories = $categoriesRepository->findBy(['parent' => $categories],['categoryOrder' => 'asc']);

        //dd($LesCategories);

        $message = "";

        if($LesCategories === []){

            $LesProduits = $productsRepository->findBy(['Categories' => $categories],['id' => 'asc']);

            //dd($LesProduits);

            if($LesProduits === []){
                $entityManager->remove($categories);

                $entityManager->flush();

                return $this->redirectToRoute('categories_index');
            }
            $message = "Impossible de supprimer cette catégorie car elle contient des produits";
        }else{
            $message = "Impossible de supprimer cette catégorie car elle parente d'une autre";
        }

        $ListeCategories = $categoriesRepository->findBy([],['categoryOrder' => 'asc']);

        return $this->render('admin/categories/index.html.twig',[
            'categories' => $ListeCategories,
            'error'=> $message
        ]);
    }
}