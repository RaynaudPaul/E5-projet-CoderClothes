<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UsersRepository;

use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Action pour l'affichage de la page de connexion.
     *
     * @param AuthenticationUtils $authenticationUtils L'outil d'authentification
     * @return Response La réponse HTTP
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('main');
        }

        // Récupérer les éventuelles erreurs de connexion
        $error = $authenticationUtils->getLastAuthenticationError();
        // Récupérer le dernier nom d'utilisateur saisi par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('/security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }


    /**
     * Action de déconnexion.
     *
     * Cette méthode peut être vide, elle sera interceptée par la clé de déconnexion de votre pare-feu.
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Action pour gérer la demande de réinitialisation de mot de passe.
     *
     * @param Request                   $request                L'objet Request
     * @param UsersRepository           $usersRepository        Le référentiel des utilisateurs
     * @param TokenGeneratorInterface   $tokenGenerator         L'interface de génération de jeton
     * @param EntityManagerInterface   $entityManager          L'interface de gestionnaire d'entité
     * @param SendMailService           $mail                   Le service d'envoi de courrier
     * @return Response                 La réponse HTTP
     */
    #[Route(path: '/oubli-pass', name: 'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UsersRepository $usersRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        SendMailService $mail
    ): Response {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Rechercher l'utilisateur par son email
            $user = $usersRepository->findOneByEmail($form->get('email')->getData());

            // Vérifier si l'utilisateur existe
            if ($user) {
                // Générer un jeton de réinitialisation
                $token = $tokenGenerator->generateToken();
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                // Générer un lien de réinitialisation du mot de passe
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // Créer les données du mail
                $context = compact('url', 'user');

                // Envoyer le mail
                $mail->send(
                    'no-reply@e-commerce.fr',
                    $user->getEmail(),
                    'Réinitialisation de mot de passe',
                    'password_reset',
                    $context
                );

                $this->addFlash('success', 'Email envoyé avec succès');
                return $this->redirectToRoute('app_login');
            }

            $this->addFlash('danger', 'Un problème est survenu');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createview()
        ]);
    }

    /**
     * Action pour réinitialiser le mot de passe.
     *
     * @param string                     $token                Le jeton de réinitialisation
     * @param Request                    $request              La requête HTTP
     * @param UsersRepository            $usersRepository      Le référentiel des utilisateurs
     * @param EntityManagerInterface     $entityManager       L'interface de gestionnaire d'entités
     * @param UserPasswordHasherInterface $passwordHasher       L'interface de hachage des mots de passe des utilisateurs
     * @return Response                  La réponse HTTP
     */
    #[Route('/oubli-pass/{token}', name: 'reset_pass')]
    public function resetPass(
        string $token,
        Request $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        // On vérifie si on a ce token dans la base
        $user = $usersRepository->findOneByResetToken($token);

        if ($user) {
            $form = $this->createForm(ResetPasswordFormType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // On efface le token
                $user->setResetToken('');
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Mot de passe changé avec succès');
                return $this->redirectToRoute('app_login');
            }

            return $this->render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]);
        }

        $this->addFlash('danger', 'Jeton invalide');
        return $this->redirectToRoute('app_login');
    }

}
