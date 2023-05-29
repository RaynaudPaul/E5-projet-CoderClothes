<?php

namespace App\Controller\Admin;

use App\Entity\Images;
use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Repository\ImagesRepository;
use App\Repository\ProductsRepository;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;

use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Définition de la classe ProductsController, qui étend AbstractController
 * et gère les routes pour les produits de l'administration.
 */
#[Route('/admin/produits', name: 'app_admin_products_')]
class ProductsController extends AbstractController
{
    /**
     * Action pour afficher la liste des produits.
     *
     * @param ProductsRepository $productsRepository Le repository des produits
     * @param ImagesRepository   $imagesRepository   Le repository des images
     * @return Response La réponse HTTP
     */
    #[Route('/', name: 'index')]
    public function index(ProductsRepository $productsRepository, ImagesRepository $imagesRepository): Response
    {
        // Récupérer les produits triés par 'id' de manière ascendante
        $products = $productsRepository->findBy([], ['id' => 'asc']);

        // Récupérer les images associées à chaque produit
        foreach ($products as $product) {
            $image = $imagesRepository->findBy(['products' => $product]);

            $product->addImage($image[0]);
        }

        // Rendre la vue 'admin/products/index.html.twig' en passant les produits comme variable
        return $this->render('admin/products/index.html.twig', compact('products'));
    }

    /**
     * Action pour ajouter un nouveau produit.
     *
     * @param Request                $request         La requête HTTP
     * @param EntityManagerInterface $em              L'interface de gestionnaire d'entité
     * @param SluggerInterface       $slugger         L'interface du slugger
     * @param PictureService         $pictureService  Le service de gestion des images
     * @return Response La réponse HTTP
     */
    #[Route('/ajout', name: 'add')]
    public function add(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        PictureService $pictureService
    ): Response {
        // Vérifier si l'utilisateur a le rôle d'administrateur
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Créer une nouvelle instance de Products
        $product = new Products();

        // Créer le formulaire en utilisant ProductsFormType et passer le produit en tant que modèle
        $productForm = $this->createForm(ProductsFormType::class, $product);

        $productForm->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($productForm->isSubmitted() && $productForm->isValid()) {
            // Récupérer les images du formulaire
            $images = $productForm->get('images')->getData();

            // Inverser l'ordre des images pour les associer correctement au produit
            $images = array_reverse($images);

            foreach ($images as $image) {
                $folder = 'products';

                // Ajouter l'image et récupérer son nom
                $fichier = $pictureService->add($image, $folder, 300, 300);

                // Créer une nouvelle instance d'Images et associer le nom de l'image
                $img = new Images();
                $img->setName($fichier);

                // Ajouter l'image au produit
                $product->addImage($img);
            }

            // Générer le slug à partir du nom du produit
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            $product->setCreatedAt(new \DateTimeImmutable());

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit ajouté avec succès');

            return $this->redirectToRoute('app_admin_products_index');
        }

        return $this->render('admin/products/add.html.twig', [
            'productForm' => $productForm->createView()
        ]);
    }

    /**
     * Action pour éditer un produit existant.
     *
     * @param Products               $product           Le produit à éditer
     * @param Request                $request           La requête HTTP
     * @param EntityManagerInterface $em                L'interface de gestionnaire d'entité
     * @param SluggerInterface       $slugger           L'interface du slugger
     * @param ImagesRepository       $imagesRepository  Le repository des images
     * @param PictureService         $pictureService    Le service de gestion des images
     * @return Response La réponse HTTP
     */
    #[Route('/edition/{id}', name: 'edit')]
    public function edit(
        Products $product,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        ImagesRepository $imagesRepository,
        PictureService $pictureService
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', $product);

        $Images = $imagesRepository->findBy(['products' => $product]);

        foreach ($Images as $image) {
            $product->addImage($image);
        }

        $productForm = $this->createForm(ProductsFormType::class, $product);

        $productForm->handleRequest($request);

        if ($productForm->isSubmitted() && $productForm->isValid()) {
            $images = $productForm->get('images')->getData();
            foreach ($images as $image) {
                $folder = 'products';

                $fichier = $pictureService->add($image, $folder, 300, 300);

                $img = new Images();
                $img->setName($fichier);
                $product->addImage($img);
            }
            $slug = $slugger->slug($product->getName());
            $product->setSlug($slug);

            $product->setCreatedAt(new \DateTimeImmutable());

            $em->persist($product);
            $em->flush();

            $this->addFlash('success', 'Produit modifié avec succès');

            return $this->redirectToRoute('app_admin_products_index');
        }
        return $this->render('admin/products/edit.html.twig', [
            'productForm' => $productForm->createView(),
            'product' => $product
        ]);
    }

    /**
     * Action pour supprimer un produit existant.
     *
     * @param Products                $product            Le produit à supprimer
     * @param EntityManagerInterface $entityManager     L'interface de gestionnaire d'entité
     * @param ImagesRepository        $imagesRepository   Le repository des images
     * @param ProductsRepository      $productsRepository Le repository des produits
     * @param PictureService          $pictureService     Le service de gestion des images
     * @return Response La réponse HTTP
     */
    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(
        Products $product,
        EntityManagerInterface $entityManager,
        ImagesRepository $imagesRepository,
        ProductsRepository $productsRepository,
        PictureService $pictureService
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN', $product);

        $Images = $imagesRepository->findBy(['products' => $product]);

        foreach ($Images as $image) {
            $product->addImage($image);

            // Supprimer l'image associée au produit
            $pictureService->delete($image->getName(), 'products', 300, 300);
        }

        $entityManager->remove($product);
        $entityManager->flush();

        $products = $productsRepository->findBy([], ['id' => 'asc']);

        foreach ($products as $aProduct) {
            $image = $imagesRepository->findBy(['products' => $product]);

            $aProduct->addImage($image[0]);
        }

        return $this->render('admin/products/index.html.twig', compact('products'));
    }

    /**
     * Action pour supprimer une image d'un produit.
     *
     * @param Images                  $image             L'image à supprimer
     * @param Request                 $request           La requête HTTP
     * @param EntityManagerInterface $em                L'interface de gestionnaire d'entité
     * @param PictureService          $pictureService    Le service de gestion des images
     * @return JsonResponse La réponse JSON
     */
    #[Route('/suppression/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(
        Images $image,
        Request $request,
        EntityManagerInterface $em,
        PictureService $pictureService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            $nom = $image->getName();

            if ($pictureService->delete($nom, 'products', 300, 300)) {
                $em->remove($image);
                $em->flush();

                return new JsonResponse(['success' => true], 200);
            }
            return new JsonResponse(['error' => 'Erreur de suppression'], 400);
        }

        return new JsonResponse(['error' => 'Token invalide'], 400);
    }
}
