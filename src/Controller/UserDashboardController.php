<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class UserDashboardController extends AbstractController
{
    /**
     * @Route("/user/dashboard", name="app_user_dashboard", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('user_dashboard/index.html.twig');
    }

    // TOURNAMENTS


    /**
     * @Route("/user/tournaments/create", name="user_create_tournament", methods={"POST"})
     */
    public function createTournament(Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        // Collect data from the form submission
        $data = $request->request->all();

        // Send a POST request to the API to create the tournament
        $response = $client->request('POST', 'http://localhost:8000/api/tournaments', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => [
                'name' => $data['tournament_name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'location' => $data['location'],
                'description' => $data['description'],
                'max_participants' => $data['max_participants'],
                'status' => $data['status'],
                'sport' => $data['sport'],
            ],
        ]);

        if ($response->getStatusCode() === 201) {
            $this->addFlash('success', 'Tournoi créé avec succès!');
        } else {
            $this->addFlash('error', 'Une erreur est survenue lors de la création du tournoi.');
        }

        return $this->redirectToRoute('app_user_dashboard');
    }

    /**
     * @Route("/user/tournaments/get", name="user_get_all_tournaments", methods={"POST"})
     */
    public function getAllTournaments(Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $response = $client->request('GET', 'http://localhost:8000/api/tournaments', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $tournaments = $response->toArray();

        return $this->render('user_dashboard/index.html.twig', ['tournaments' => $tournaments]);
    }

    /**
     * @Route("/user/tournaments/{id}/register", name="user_join_tournament", methods={"POST"})
     */
    public function joinTournament(int $id, Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $data = $request->request->all();

        $response = $client->request('POST', "http://localhost:8000/api/tournaments/$id/registrations", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => [
                'player_id' => $this->getUser()->getId(),
            ],
        ]);

        if ($response->getStatusCode() === 201) {
            $this->addFlash('success', 'Vous avez rejoint le tournoi avec succès!');
        } else {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'inscription.');
        }

        return $this->redirectToRoute('app_user_dashboard');
    }

    /**
     * @Route("/user/tournaments/{id}/unregister", name="user_leave_tournament", methods={"POST"})
     */
    public function leaveTournament(int $id, Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $response = $client->request('DELETE', "http://localhost:8000/api/tournaments/$id/registrations", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => [
                'player_id' => $this->getUser()->getId(),
            ],
        ]);

        if ($response->getStatusCode() === 200) {
            $this->addFlash('success', 'Vous avez quitté le tournoi avec succès!');
        } else {
            $this->addFlash('error', 'Une erreur est survenue lors de la sortie du tournoi.');
        }

        return $this->redirectToRoute('app_user_dashboard');
    }

    // MATCHES

    /**
     * @Route("/user/tournaments/{id}/matches", name="user_get_matches", methods={"POST"})
     */
    public function getMatches(int $id, Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $response = $client->request('GET', "http://localhost:8000/api/tournaments/$id/sport-matchs", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $matches = $response->toArray();

        return $this->render('user_dashboard/index.html.twig', ['matches' => $matches]);
    }

    /**
     * @Route("/user/matches/create", name="user_create_match", methods={"POST"})
     */
    public function createMatch(Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');
        
        // Collect match data from the form submission
        $data = $request->request->all();
    
        // Send a POST request to the API to create the match
        $response = $client->request('POST', 'http://localhost:8000/api/matches', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => [
                'tournament_id' => $data['tournament_id'],
                'player1_id' => $data['player1_id'],
                'player2_id' => $data['player2_id'],
                'match_date' => $data['match_date'],
                'score_player1' => $data['score_player1'],
                'score_player2' => $data['score_player2'],
                'status' => $data['status'],
            ],
        ]);
    
        if ($response->getStatusCode() === 201) {
            $this->addFlash('success', 'Match créé avec succès!');
        } else {
            $this->addFlash('error', 'Une erreur est survenue lors de la création du match.');
        }
    
        return $this->redirectToRoute('app_user_dashboard');
    }


    /**
     * @Route("/user/matches/{id}", name="user_modify_match", methods={"PUT"})
     */
    public function modifyMatch(int $id, Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $data = $request->request->all();

        $response = $client->request('PUT', "http://localhost:8000/api/tournaments/$id/sport-matchs/$id", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => $data
        ]);

        return $this->redirectToRoute('app_user_dashboard');
    }

    /**
     * @Route("/user/matches/{id}", name="user_delete_match", methods={"DELETE"})
     */
    public function deleteMatch(int $id, Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $response = $client->request('DELETE', "http://localhost:8000/api/tournaments/$id/sport-matchs/$id", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return $this->redirectToRoute('app_user_dashboard');
    }
}
