<?php

namespace App\Controller\Api;

use App\Entity\Registration;
use App\Entity\User;
use App\Entity\Tournament;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/tournaments', name: 'api_tournament_')]
class RegistrationController extends AbstractController
{
    #[Route('/{id}/registrations', name: 'get_registrations', methods: ['GET'])]
    public function getRegistrationsForTournament(int $id, EntityManagerInterface $em): JsonResponse
    {
        $tournament = $em->getRepository(Tournament::class)->find($id);
        if (!$tournament) {
            return $this->json(['message' => 'Tournament not found'], Response::HTTP_NOT_FOUND);
        }

        $registrations = $em->getRepository(Registration::class)->findBy(['tournament' => $tournament]);
        $data = [];

        foreach ($registrations as $registration) {
            $data[] = [
                'id' => $registration->getId(),
                'player_id' => $registration->getPlayer()->getId(),
                'tournament_id' => $registration->getTournament()->getId(),
                'registration_date' => $registration->getRegistrationDate()->format('Y-m-d'),
                'status' => $registration->getStatus(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}/registrations', name: 'register_user', methods: ['POST'])]
    public function registerUserToTournament(int $id, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $tournament = $em->getRepository(Tournament::class)->find($id);
        if (!$tournament) {
            return $this->json(['message' => 'Tournament not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        $player = $em->getRepository(User::class)->find($data['player_id'] ?? null);

        if (!$player) {
            return $this->json(['message' => 'Player not found'], Response::HTTP_NOT_FOUND);
        }

        $registration = new Registration();
        $registration->setPlayer($player);
        $registration->setTournament($tournament);
        $registration->setRegistrationDate(new \DateTime());
        $registration->setStatus($data['status'] ?? 'pending');

        $em->persist($registration);
        $em->flush();

        return $this->json(['message' => 'Registration successful'], Response::HTTP_CREATED);
    }

    #[Route('/{idTournament}/registrations/{idRegistration}', name: 'delete_registration', methods: ['DELETE'])]
    public function deleteRegistration(int $idTournament, int $idRegistration, EntityManagerInterface $em): JsonResponse
    {
        $registration = $em->getRepository(Registration::class)->find($idRegistration);

        if (!$registration || $registration->getTournament()->getId() !== $idTournament) {
            return $this->json(['message' => 'Registration not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($registration);
        $em->flush();

        return $this->json(['message' => 'Registration deleted']);
    }
}
