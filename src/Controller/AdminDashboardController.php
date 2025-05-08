<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Messenger\MessageBusInterface; 
use App\Message\AdminNotification; 

class AdminDashboardController extends AbstractController
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @Route("/admin/dashboard", name="app_admin_dashboard", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('admin_dashboard/index.html.twig');
    }

    // USERS

    /**
     * @Route("/admin/users/get", name="admin_get_all_users", methods={"POST"})
     */
    public function getAllUsers(Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $response = $client->request('GET', 'http://localhost:8000/api/players', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $users = $response->toArray();

        return $this->render('admin_dashboard/index.html.twig', ['users' => $users]);
    }

    /**
     * @Route("/admin/users/update", name="admin_modify_user", methods={"POST"})
     */
    public function modifyUser(Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $id = $request->request->get('id');
        $data = $request->request->all();

        $client->request('PUT', "http://localhost:8000/api/players/$id", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => $data
        ]);

        
        $this->bus->dispatch(new AdminNotification('User updated', ['user_id' => $id, 'changes' => $data]));

        return $this->redirectToRoute('app_admin_dashboard');
    }

    /**
     * @Route("/admin/users/delete", name="admin_delete_user", methods={"POST"})
     */
    public function deleteUser(Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $id = $request->request->get('id');

        $client->request('DELETE', "http://localhost:8000/api/players/$id", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        
        $this->bus->dispatch(new AdminNotification('User deleted', ['user_id' => $id]));

        return $this->redirectToRoute('app_admin_dashboard');
    }

    // TOURNAMENTS

    /**
     * @Route("/admin/tournaments/get", name="admin_get_all_tournaments", methods={"POST"})
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

        return $this->render('admin_dashboard/index.html.twig', ['tournaments' => $tournaments]);
    }

    /**
     * @Route("/admin/tournaments/update", name="admin_modify_tournament", methods={"POST"})
     */
    public function modifyTournament(Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $id = $request->request->get('id');
        $data = $request->request->all();

        $client->request('PUT', "http://localhost:8000/api/tournaments/$id", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => $data
        ]);

        
        $this->bus->dispatch(new AdminNotification('Tournament updated', ['tournament_id' => $id, 'changes' => $data]));

        return $this->redirectToRoute('app_admin_dashboard');
    }

    /**
     * @Route("/admin/tournaments/delete", name="admin_delete_tournament", methods={"POST"})
     */
    public function deleteTournament(Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $id = $request->request->get('id');

        $client->request('DELETE', "http://localhost:8000/api/tournaments/$id", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        
        $this->bus->dispatch(new AdminNotification('Tournament deleted', ['tournament_id' => $id]));

        return $this->redirectToRoute('app_admin_dashboard');
    }

    // MATCHES

    /**
     * @Route("/admin/matches/get", name="admin_get_matches", methods={"POST"})
     */
    public function getMatches(Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $tournamentId = $request->request->get('tournament_id');

        $response = $client->request('GET', "http://localhost:8000/api/tournaments/$tournamentId/matches", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $matches = $response->toArray();

        return $this->render('admin_dashboard/index.html.twig', ['matches' => $matches]);
    }

    /**
     * @Route("/admin/matches/update", name="admin_modify_match", methods={"POST"})
     */
    public function modifyMatch(Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $id = $request->request->get('match_id');
        $data = $request->request->all();

        $client->request('PUT', "http://localhost:8000/api/matches/$id", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => $data
        ]);

        
        $this->bus->dispatch(new AdminNotification('Match updated', ['match_id' => $id, 'changes' => $data]));

        return $this->redirectToRoute('app_admin_dashboard');
    }

    /**
     * @Route("/admin/matches/delete", name="admin_delete_match", methods={"POST"})
     */
    public function deleteMatch(Request $request, HttpClientInterface $client): Response
    {
        $token = $request->getSession()->get('jwt_token');

        $id = $request->request->get('match_id');

        $client->request('DELETE', "http://localhost:8000/api/matches/$id", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        
        $this->bus->dispatch(new AdminNotification('Match deleted', ['match_id' => $id]));

        return $this->redirectToRoute('app_admin_dashboard');
    }
}