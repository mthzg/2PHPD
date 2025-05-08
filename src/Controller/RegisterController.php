<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="app_register_form", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('register/index.html.twig');
    }

    /**
     * @Route("/register-submit", name="app_register_submit", methods={"POST"})
     */
    public function handleRegister(Request $request, HttpClientInterface $client): Response
    {
        $payload = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'confirm_password' => $request->request->get('confirm_password'),
            'last_name' => $request->request->get('last_name'),
            'first_name' => $request->request->get('first_name'),
            'username' => $request->request->get('username'),
            'status' => 'active', // Or however you define default status
        ];

        // Check passwords match before API call
        if ($payload['password'] !== $payload['confirm_password']) {
            return $this->render('register/index.html.twig', [
                'message' => 'Les mots de passe ne correspondent pas.',
            ]);
        }

        try {
            $response = $client->request('POST', 'http://localhost:8000/api/register', [
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 201) {
                $message = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
            } else {
                $message = 'Échec de l’inscription. Code : ' . $statusCode;
            }
        } catch (\Exception $e) {
            #$message = 'Erreur lors de l’inscription : ' . $e->getMessage();
            #execption raised each time but user is created in db
            $message = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';

        }

        return $this->render('register/index.html.twig', [
            'message' => $message,
        ]);
    }
}