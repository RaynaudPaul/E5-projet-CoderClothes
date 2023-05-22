<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/produits', name: 'app_admin_products_')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/products/index.html.twig');
    }

    #[Route('/ajout', name: 'add')]
    public function add(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        return $this->render('admin/products/index.html.twig');
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Products $product): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EDIT', $product);
        return $this->render('admin/products/index.html.twig');
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(Products $product): Response
    {
        $this->denyAccessUnlessGranted('ROLE_DELETE', $product);
        return $this->render('admin/products/index.html.twig');
    }
}
