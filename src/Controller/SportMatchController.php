<?php

namespace App\Controller;

use App\Entity\SportMatch;
use App\Entity\Tournament;
use App\Repository\SportMatchRepository;
use App\Repository\TournamentRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface; 
use App\Message\MatchUpdateNotification; 

class SportMatchController extends AbstractController
{
    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    /**
     * @Route("/api/tournaments/{tournamentId}/sport-matchs", name="api_tournament_get_matches", methods={"GET"})
     */
    public function getMatchesByTournament(int $tournamentId, SportMatchRepository $matchRepo): JsonResponse
    {
        $matches = $matchRepo->findBy(['tournament' => $tournamentId]);

        $data = array_map([$this, 'serializeMatch'], $matches);

        return $this->json($data);
    }

    /**
     * @Route("/api/tournaments/{tournamentId}/sport-matchs/{matchId}", name="api_tournament_get_match_detail", methods={"GET"})
     */
    public function getMatchDetail(int $matchId, SportMatchRepository $matchRepo): JsonResponse
    {
        $match = $matchRepo->find($matchId);

        if (!$match) {
            return $this->json(['message' => 'Match not found'], 404);
        }

        return $this->json($this->serializeMatch($match));
    }

    /**
     * @Route("/api/tournaments/{tournamentId}/sport-matchs", name="api_tournament_create_match", methods={"POST"})
     */
    public function createMatch(
        int $tournamentId,
        Request $request,
        EntityManagerInterface $em,
        TournamentRepository $tournamentRepo,
        UserRepository $userRepo
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $tournament = $tournamentRepo->find($tournamentId);

        if (!$tournament) {
            return $this->json(['message' => 'Tournament not found'], 404);
        }

        $player1 = $userRepo->find($data['player1_id']);
        $player2 = $userRepo->find($data['player2_id']);

        if (!$player1 || !$player2) {
            return $this->json(['message' => 'One or both players not found'], 404);
        }

        $match = new SportMatch();
        $match->setTournament($tournament);
        $match->setPlayer1($player1);
        $match->setPlayer2($player2);
        $match->setMatchDate(new \DateTime($data['match_date']));
        $match->setScorePlayer1($data['score_player1']);
        $match->setScorePlayer2($data['score_player2']);
        $match->setStatus($data['status']);

        $em->persist($match);
        $em->flush();

        return $this->json($this->serializeMatch($match), 201);
    }

    /**
     * @Route("/api/tournaments/{tournamentId}/sport-matchs/{matchId}", name="api_tournament_update_match", methods={"PUT"})
     */
    public function updateMatch(
        int $matchId,
        Request $request,
        EntityManagerInterface $em,
        SportMatchRepository $matchRepo,
        UserRepository $userRepo
    ): JsonResponse {
        $match = $matchRepo->find($matchId);

        if (!$match) {
            return $this->json(['message' => 'Match not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['player1_id'])) {
            $player1 = $userRepo->find($data['player1_id']);
            $match->setPlayer1($player1);
        }

        if (isset($data['player2_id'])) {
            $player2 = $userRepo->find($data['player2_id']);
            $match->setPlayer2($player2);
        }

        if (isset($data['match_date'])) {
            $match->setMatchDate(new \DateTime($data['match_date']));
        }

        if (isset($data['score_player1'])) {
            $match->setScorePlayer1($data['score_player1']);
        }

        if (isset($data['score_player2'])) {
            $match->setScorePlayer2($data['score_player2']);
        }

        if (isset($data['status'])) {
            $match->setStatus($data['status']);
        }

        $em->flush();

        
        $this->bus->dispatch(new MatchUpdateNotification($match));

        return $this->json($this->serializeMatch($match));
    }

    /**
     * @Route("/api/tournaments/{tournamentId}/sport-matchs/{matchId}", name="api_tournament_delete_match", methods={"DELETE"})
     */
    public function deleteMatch(int $matchId, SportMatchRepository $matchRepo, EntityManagerInterface $em): JsonResponse
    {
        $match = $matchRepo->find($matchId);

        if (!$match) {
            return $this->json(['message' => 'Match not found'], 404);
        }

        $em->remove($match);
        $em->flush();

        return $this->json(['message' => 'Match deleted']);
    }

    private function serializeMatch(SportMatch $match): array
    {
        return [
            'id' => $match->getId(),
            'tournament_id' => $match->getTournament() ? $match->getTournament()->getId() : null,
            'player1_id' => $match->getPlayer1() ? $match->getPlayer1()->getId() : null,
            'player2_id' => $match->getPlayer2() ? $match->getPlayer2()->getId() : null,
            'match_date' => $match->getMatchDate() ? $match->getMatchDate()->format('Y-m-d') : null,
            'score_player1' => $match->getScorePlayer1(),
            'score_player2' => $match->getScorePlayer2(),
            'status' => $match->getStatus(),
        ];
    }
}