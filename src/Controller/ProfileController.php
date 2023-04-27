<?php

namespace App\Controller;

use App\Form\ProfileFormType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/profil', name: 'app_profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(Request $request,UsersRepository $usersRepository, TokenStorageInterface $tokenStorage,EntityManagerInterface $entityManager): Response
    {
        //$user = $authenticationUtils->getLastUsername();

        $token = $tokenStorage->getToken();

        // Vérifier si un utilisateur est connecté
        if ($token && $token->getUser()) {
            // Récupérer l'objet User
            $user = $token->getUser();

            //dd($user);

            $form = $this->createForm(ProfileFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // encode the plain password
                /*$user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );*/

                $entityManager->persist($user);
                $entityManager->flush();
                // do anything else you need here, like send an email


            }

            return $this->render('profile/index.html.twig', [
                'controller_name' => 'Profile de l\'utilisateur',
                'user' => $user,
                'profileForm' => $form->createView(),
            ]);
        }

    return $this->render("");

    }

    #[Route('/commandes', name: 'orders')]
    public function orders(): Response
    {
        return $this->render('profile/orders.html.twig', [
            'controller_name' => 'Commandes de l\'utilisateur',
        ]);
    }
}
