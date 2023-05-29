<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UsersAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Définition de la classe RegistrationController, qui étend AbstractController
 * et gère les routes pour l'inscription.
 */
class RegistrationController extends AbstractController
{
    /**
     * Action pour l'inscription d'un utilisateur.
     *
     * @param Request                       $request                    La requête HTTP
     * @param UserPasswordHasherInterface   $userPasswordHasher         L'interface de hachage des mots de passe utilisateur
     * @param UserAuthenticatorInterface    $userAuthenticator          L'interface d'authentification utilisateur
     * @param UsersAuthenticator            $authenticator              L'authentificateur utilisateur
     * @param EntityManagerInterface       $entityManager             L'interface de gestionnaire d'entités
     * @param SendMailService               $mail                       Le service d'envoi de mails
     * @param JWTService                    $jwt                        Le service JWT
     * @return Response                     La réponse HTTP
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UsersAuthenticator $authenticator, EntityManagerInterface $entityManager, SendMailService $mail, JWTService $jwt): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('main');
        }

        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // Génération du JWT de l'utilisateur
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            $payload = [
                'user_id' => $user->getId()
            ];

            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // Envoi d'un e-mail
            $mail->send(
                'no-reply@monsite.net',
                $user->getEmail(),
                'Activation de votre compte sur le site e-commerce',
                'register',
                compact('user', 'token')
            );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    /**
     * Action pour l'inscription d'un utilisateur.
     *
     * @param                               $token                      Récupère le token
     * @param JWTService                    $jwt                        Le service JWT
     * @param UsersRepository               $usersRepository            Le dépôt des utilisateurs
     * @param EntityManagerInterface        $entityManager              L'interface de gestionnaire d'entité
     * @return Response                     La réponse HTTP
     */
    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt,
                               UsersRepository $usersRepository,
                               EntityManagerInterface $em): Response
    {
        // Vérifier si le token est valide, non expiré et non modifié
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            // Récupérer les informations du token (payload)
            $payload = $jwt->getPayload($token);

            // Récupérer l'utilisateur associé au token
            $user = $usersRepository->find($payload['user_id']);

            // Vérifier si l'utilisateur existe et n'a pas encore activé son compte
            if ($user && !$user->getIsVerified()) {
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('main');
            }
        }

        // Le token est invalide ou a expiré
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }


    /**
     * Action pour l'inscription d'un utilisateur.
     *
     * @param JWTService                    $jwt                        Le service JWT
     * @param SendMailService               $email                      L'interface de gestionnaire de mail
     * @param UsersRepository               $usersRepository            Le dépôt des utilisateurs
     * @return Response                     La réponse HTTP
     */
    #[Route('/renvoiverif', name: 'resend_verif')]
    public function resendVerif(JWTService $jwt,
                                SendMailService $mail,
                                UsersRepository $usersRepository): Response
    {
        $user = $this->getUser();

        if(!$user){
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        if($user->getIsVerified()){
            $this->addFlash('warning', 'Cet utilisateur est déjà activé');
            return $this->redirectToRoute('profile_index');
        }

        // On génère le JWT de l'utilisateur
        // On crée le Header
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        // On crée le Payload
        $payload = [
            'user_id' => $user->getId()
        ];

        // On génère le token
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        // On envoie un mail
        $mail->send(
            'no-reply@monsite.net',
            $user->getEmail(),
            'Activation de votre compte sur le site e-commerce',
            'register',
            compact('user', 'token')
        );
        $this->addFlash('success', 'Email de vérification envoyé');
        return $this->redirectToRoute('profile_index');
    }


}
