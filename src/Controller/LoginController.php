<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @Route("/login-submit", name="app_login_submit", methods={"POST"})
     */
    public function handleLogin_test(Request $request, HttpClientInterface $client): Response
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        try {
            $apiResponse = $client->request('POST', 'http://127.0.0.1:8000/api/login', [
                'json' => [
                    "email" => $email,
                    "password" => $password,
                ],
            ]);

            $status = $apiResponse->getStatusCode();

            if ($status === 200) {
                $data = $apiResponse->toArray();
                $message = 'Connexion réussie ! Token : ' . $data['token'];
                print("here");
                print($message);
            } else {
                $message = 'Échec de la connexion. Code: ' . $status;
            }
        } catch (\Exception $e) {
            $message = 'Erreur : ' . $e->getMessage();
        }

        return $this->render('login/index.html.twig', [
            'message' => $message,
        ]);
    }
}
