<?php

namespace App\Controller;

use App\Entity\Tournament;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface; // Ajout pour la gestion des messages
use App\Message\TournamentWinnerNotification; // Classe de notification à créer

class TournamentController extends AbstractController
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @Route("/api/tournaments", name="api_tournament_list", methods={"GET"})
     */
    public function getAllTournaments(EntityManagerInterface $em): JsonResponse
    {
        $tournaments = $em->getRepository(Tournament::class)->findAll();
        $data = [];

        foreach ($tournaments as $tournament) {
            $data[] = $this->serializeTournament($tournament);
        }

        return $this->json($data);
    }

    /**
     * @Route("/api/tournaments/{id}", name="api_tournament_get_by_id", methods={"GET"})
     */
    public function getTournamentById(int $id, EntityManagerInterface $em): JsonResponse
    {
        $tournament = $em->getRepository(Tournament::class)->find($id);
        if (!$tournament) {
            return $this->json(['message' => 'Tournament not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->serializeTournament($tournament));
    }

    /**
     * @Route("/api/tournaments", name="api_tournament_create", methods={"POST"})
     */
    public function createTournament(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $organizer = $em->getRepository(User::class)->find($data['organizer_id'] ?? null);
        if (!$organizer) {
            return $this->json(['message' => 'Organizer not found'], Response::HTTP_NOT_FOUND);
        }

        $tournament = new Tournament();
        $tournament->setTournamentName($data['tournament_name']);
        $tournament->setStartDate(new \DateTime($data['start_date']));
        $tournament->setEndDate(new \DateTime($data['end_date']));
        $tournament->setLocation($data['location'] ?? null);
        $tournament->setDescription($data['description']);
        $tournament->setMaxParticipants($data['max_participants']);
        $tournament->setStatus($data['status']);
        $tournament->setSport($data['sport']);
        $tournament->setOrganizer($organizer);

        $em->persist($tournament);
        $em->flush();

        return $this->json(['message' => 'Tournament created', 'id' => $tournament->getId()], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/tournaments/{id}", name="api_tournament_update", methods={"PUT"})
     */
    public function updateTournament(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $tournament = $em->getRepository(Tournament::class)->find($id);
        if (!$tournament) {
            return $this->json(['message' => 'Tournament not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['tournament_name'])) $tournament->setTournamentName($data['tournament_name']);
        if (isset($data['start_date'])) $tournament->setStartDate(new \DateTime($data['start_date']));
        if (isset($data['end_date'])) $tournament->setEndDate(new \DateTime($data['end_date']));
        if (isset($data['location'])) $tournament->setLocation($data['location']);
        if (isset($data['description'])) $tournament->setDescription($data['description']);
        if (isset($data['max_participants'])) $tournament->setMaxParticipants($data['max_participants']);
        if (isset($data['status'])) $tournament->setStatus($data['status']);
        if (isset($data['sport'])) $tournament->setSport($data['sport']);

        if (isset($data['winner_id'])) {
            $winner = $em->getRepository(User::class)->find($data['winner_id']);
            if ($winner) {
                $tournament->setWinner($winner);

                // Envoyer une notification à tous les participants
                $participants = $em->getRepository(User::class)->findByTournament($id);
                foreach ($participants as $participant) {
                    $this->bus->dispatch(new TournamentWinnerNotification($participant, $winner, $tournament));
                }
            }
        }

        $em->flush();

        return $this->json(['message' => 'Tournament updated']);
    }

    /**
     * @Route("/api/tournaments/{id}", name="api_tournament_delete", methods={"DELETE"})
     */
    public function deleteTournament(int $id, EntityManagerInterface $em): JsonResponse
    {
        $tournament = $em->getRepository(Tournament::class)->find($id);
        if (!$tournament) {
            return $this->json(['message' => 'Tournament not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($tournament);
        $em->flush();

        return $this->json(['message' => 'Tournament deleted']);
    }

    private function serializeTournament(Tournament $tournament): array
    {
        return [
            'id' => $tournament->getId(),
            'organizer_id' => $tournament->getOrganizer() ? $tournament->getOrganizer()->getId() : null,
            'winner_id' => $tournament->getWinner() ? $tournament->getWinner()->getId() : null,
            'tournament_name' => $tournament->getTournamentName(),
            'start_date' => $tournament->getStartDate() ? $tournament->getStartDate()->format('Y-m-d') : null,
            'end_date' => $tournament->getEndDate() ? $tournament->getEndDate()->format('Y-m-d') : null,
            'location' => $tournament->getLocation(),
            'description' => $tournament->getDescription(),
            'max_participants' => $tournament->getMaxParticipants(),
            'status' => $tournament->getStatus(),
            'sport' => $tournament->getSport(),
        ];
    }
}