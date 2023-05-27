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

#[Route('/admin/utilisateurs', name: 'app_admin_users_')]
class UsersController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(UsersRepository $usersRepository): Response
    {
        $users = $usersRepository->findBy([],['id' => 'asc']);

        return $this->render('admin/users/index.html.twig', compact('users'));
    }

    #[Route('/edition/{id}', name: 'edit')]
    public function edit(Request $request,EntityManagerInterface $entityManager,Users $users): Response
    {

        $originalRoles = $this->getParameter('security.role_hierarchy.roles');

        $roles = [];

        foreach ($originalRoles as $originalRole) {

            $name = "";

            if($originalRole[0] === "ROLE_USER"){
                $name = "Membre";
            }else if($originalRole[0] === "ROLE_PRODUCT_ADMIN"){
                $name = "Admin produits";

            }else if($originalRole[0] === "ROLE_ADMIN"){
                $name="Admin";
            }else{
                $name=$originalRole[0];
            }
            $roles[$name] = $originalRole[0];
        }

        //dd($roles);

        $choices = array('choices' => array_flip($roles));

        //dd($choices);

        //dd($originalRoles);

        $form = $this->createForm(ProfileFormType::class, $users)
            ->add('roles', ChoiceType::class, [
            'choices' => $roles,
            'required' => true,
            'multiple' => true,
            'expanded' => true, // pour afficher les choix sous forme de cases à cocher au lieu d'une liste déroulante
        ]);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $selectedRole = $form->get('roles')->getData();


            //dd($formData);

            // Récupérer la valeur sélectionnée du champ "roles"

            //dd($selectedRole);

            $users->setRoles($selectedRole);

            //dd($users->getRoles());

            //dd($users);

            $entityManager->persist($users);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_users_index');
        }

        //$entityManager->flush();

        return $this->render('admin/users/edit.html.twig', [
            'user' => $users,
            'roles'=> array_flip($roles),
            'profileForm' => $form->createView(),
        ]);
    }

    #[Route('/suppression/{id}', name: 'delete')]
    public function delete(EntityManagerInterface $entityManager,Users $users): Response
    {
        $entityManager->remove($users);

        $entityManager->flush();

        return $this->redirectToRoute('app_admin_users_index');
    }
}
