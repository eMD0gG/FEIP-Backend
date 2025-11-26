<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\AccessTokenHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AccessTokenHandler $tokenHandler
    ) {}

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $number = $data['number'] ?? null;
        $password = $data['password'] ?? null;

        if (!$number || !$password) {
            return new JsonResponse(['error' => 'Phone and password required'], 400);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['number' => $number]);

        if (!$user || !password_verify($password, $user->getPassword())) {
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        $token = $this->tokenHandler->createToken($user);

        return new JsonResponse([
            'token' => $token->getToken(),
            'expires_at' => $token->getExpiresAt()->format('c')
        ]);
    }

    #[Route('/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return new JsonResponse(['error' => 'Authorization header missing'], 400);
        }

        $tokenString = substr($authHeader, 7);
        $token = $this->tokenHandler->findToken($tokenString);

        if (!$token) {
        return new JsonResponse(
            ['error' => 'Token not found or already invalidated'],
            JsonResponse::HTTP_UNAUTHORIZED
            );
        }

        $this->entityManager->remove($token);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
