<?php

namespace App\Controller\Admin;

use App\Entity\Users;
use App\Form\ProfileFormType;
use App\Repository\UsersRepository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\UnicodeString;

/**
 * Définition de la classe UsersController, qui étend AbstractController
 * et gère les routes pour les utilisateurs de l'administration.
 */
#[Route('/admin/utilisateurs', name: 'app_admin_users_')]
class UsersController extends AbstractController
{
    /**
     * Action pour afficher la liste des utilisateurs.
     *
     * @param UsersRepository $usersRepository Le repository des utilisateurs
     * @return Response La réponse HTTP
     */
    #[Route('/', name: 'index')]
    public function index(UsersRepository $usersRepository): Response
    {
        // Récupérer les utilisateurs triés par 'id' de manière ascendante
        $users = $usersRepository->findBy([], ['id' => 'asc']);

        // Rendre la vue 'admin/users/index.html.twig' en passant les utilisateurs comme variable
        return $this->render('admin/users/index.html.twig', compact('users'));
    }

    /**
     * Action pour éditer un utilisateur existant.
     *
     * @param Request                $request                La requête HTTP
     * @param EntityManagerInterface $entityManager         L'interface de gestionnaire d'entité
     * @param Users                  $users                  L'utilisateur à éditer
     * @return Response La réponse HTTP
     */
    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, Users $users): Response
    {
        // Récupérer la hiérarchie des rôles définie dans la configuration
        $originalRoles = $this->getParameter('security.role_hierarchy.roles');

        $roles = [];

        // Parcourir les rôles originaux et les ajouter à la liste des rôles disponibles avec leur nom correspondant
        foreach ($originalRoles as $key => $originalRole) {
            $name = "";
            foreach ($originalRole as $arole) {
                if ($arole === "ROLE_USER") {
                    $name = "Membre";
                } else if ($arole === "ROLE_ORDER") {
                    $name = "Commandes";
                } else if ($arole === "ROLE_PRODUCT_ADMIN") {
                    $name = "Admin produits";
                } else if ($arole === "ROLE_ADMIN") {
                    $name = "Admin";
                } else {
                    $name = $arole;
                }
                $roles[$name] = $arole;
            }
        }

        // Créer les choix pour le champ "roles"
        $choices = array('choices' => array_flip($roles));

        // Créer le formulaire en utilisant ProfileFormType et passer l'utilisateur en tant que modèle
        $form = $this->createForm(ProfileFormType::class, $users)
            ->add('roles', ChoiceType::class, [
                'choices' => $roles,
                'required' => true,
                'multiple' => true,
                'expanded' => true, // pour afficher les choix sous forme de cases à cocher au lieu d'une liste déroulante
            ]);

        $form->handleRequest($request);

        // Vérifier si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les rôles sélectionnés
            $selectedRole = $form->get('roles')->getData();

            // Mettre à jour les rôles de l'utilisateur
            $users->setRoles($selectedRole);

            // Persister les modifications de l'utilisateur
            $entityManager->persist($users);
            $entityManager->flush();

            // Rediriger vers la liste des utilisateurs
            return $this->redirectToRoute('app_admin_users_index');
        }

        // Rendre la vue 'admin/users/edit.html.twig' en passant l'utilisateur, les rôles disponibles et le formulaire comme variables
        return $this->render('admin/users/edit.html.twig', [
            'user' => $users,
            'roles' => array_flip($roles),
            'profileForm' => $form->createView(),
        ]);
    }

    /**
     * Action pour supprimer un utilisateur existant.
     *
     * @param EntityManagerInterface $entityManager L'interface de gestionnaire d'entité
     * @param Users                  $users          L'utilisateur à supprimer
     * @return Response La réponse HTTP
     */
    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(EntityManagerInterface $entityManager, Users $users): Response
    {
        // Supprimer l'utilisateur
        $entityManager->remove($users);
        $entityManager->flush();

        // Rediriger vers la liste des utilisateurs
        return $this->redirectToRoute('app_admin_users_index');
    }
}
