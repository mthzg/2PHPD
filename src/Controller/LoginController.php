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


    /**
     * @Route("/login-submit", name="app_login_submit", methods={"POST"})
     */
    public function handleLogin(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager): Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            $message = 'Échec de la connexion : identifiants invalides.';
        } else {
            $token = $jwtManager->create($user);
            $message = 'Connexion réussie ! Token : ' . $token;
        }

        return $this->render('login/index.html.twig', [
            'message' => $message,
        ]);
    }

}
