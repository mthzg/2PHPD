<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class LoginController extends AbstractController
{
    /**
     * @Route("/login", name="app_login_form", methods={"GET"})
     */
    public function renderLogin(): Response
    {
        return $this->render('login/index.html.twig');
    }


    #/**
    # * @Route("/login-submit", name="app_login_submit", methods={"POST"})
    # */
    #
    # public function handleLogin(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager): Response
    #{
    #    $email = $request->request->get('email');
    #    $password = $request->request->get('password');
#
    #    $user = $userRepository->findOneBy(['email' => $email]);
#
    #    if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
    #        $message = 'Échec de la connexion : identifiants invalides.';
    #    } else {
    #        $token = $jwtManager->create($user);
    #        $message = 'Connexion réussie ! Token : ' . $token;
    #    }
    #    
    #    $roles = $user->getRoles();
    #    if (in_array('ROLE_ADMIN', $roles)) {
    #        return $this->render('admin_dashboard/index.html.twig', [
    #            'user' => $user,
    #            'token' => $token,
    #        ]);
    #    } elseif (in_array('ROLE_USER', $roles)) {
    #        return $this->render('user_dashboard/index.html.twig', [
    #            'user' => $user,
    #            'token' => $token,
    #        ]);
    #    }
    #}

    /**
     * @Route("/login-submit", name="app_login_submit", methods={"POST"})
     */
    public function handleLogin(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager): Response {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->render('login/index.html.twig', [
                'message' => 'Échec de la connexion : identifiants invalides.',
            ]);
        }

        $token = $jwtManager->create($user);
        $roles = $user->getRoles();
        // Stocker les infos en session si nécessaire
        $this->get('session')->set('jwt_token', $token);
        
        if (in_array('ROLE_ADMIN', $roles)) {
            return $this->redirectToRoute('app_admin_dashboard');
        } elseif (in_array('ROLE_USER', $roles)) {
            return $this->redirectToRoute('app_user_dashboard'); // À créer
        } else {
            return $this->render('login/index.html.twig', [
                'message' => 'Rôle utilisateur non reconnu.',
            ]);
        }
    }

}
