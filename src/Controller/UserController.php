<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * @Route("/api/players", name="api_user_get_all", methods={"GET"})
     */
    public function getAllUsers(EntityManagerInterface $em): JsonResponse
    {
        $users = $em->getRepository(User::class)->findAll();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'last_name' => $user->getLastName(),
                'first_name' => $user->getFirstName(),
                'username' => $user->getUsername(),
                'status' => $user->getStatus(),
            ];
        }

        return $this->json($data);
    }

    /**
     * @Route("/api/players/{id}", name="api_user_get_by_id", methods={"GET"})
     */
    public function getUserById(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'last_name' => $user->getLastName(),
            'first_name' => $user->getFirstName(),
            'username' => $user->getUsername(),
            'status' => $user->getStatus(),
        ]);
    }

    /**
     * @Route("/api/register", name="api_user_register", methods={"POST"})
     */
    public function createUser(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_USER']);
        $user->setLastName($data['last_name']);
        $user->setFirstName($data['first_name']);
        $user->setUsername($data['username']);
        $user->setStatus($data['status']);

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();

        return $this->json(['message' => 'User created successfully'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/players/{id}", name="api_user_update", methods={"PUT"})
     */
    public function updateUser(int $id, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        $user->setEmail($data['email'] ?? $user->getEmail());
        $user->setLastName($data['last_name'] ?? $user->getLastName());
        $user->setFirstName($data['first_name'] ?? $user->getFirstName());
        $user->setUsername($data['username'] ?? $user->getUsername());
        $user->setStatus($data['status'] ?? $user->getStatus());

        if (!empty($data['password'])) {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        $em->flush();

        return $this->json(['message' => 'User updated successfully']);
    }

    /**
     * @Route("/api/players/{id}", name="api_user_delete", methods={"DELETE"})
     */
    public function deleteUser(int $id, EntityManagerInterface $em): JsonResponse
    {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($user);
        $em->flush();

        return $this->json(['message' => 'User deleted successfully']);
    }
}
