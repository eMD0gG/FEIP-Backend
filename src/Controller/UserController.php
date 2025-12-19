<?php

namespace App\Controller;

use App\DTO\CreateUserDto;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    public function __construct(
        private UserService $userService,
    ) {
    }

    #[Route('/api/user', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $content = $request->getContent();
        if (empty($content)) {
            return new JsonResponse(
                ['error' => 'Request body is empty'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $data = json_decode($content, true);

        if (!isset($data['name']) || !isset($data['number']) || !isset($data['password'])) {
            return new JsonResponse(
                ['error' => 'Missing required fields: name, number and password'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user = new CreateUserDto(
            $data['name'],
            $data['password'],
            $data['number'],
        );

        try {
            $this->userService->createUser($user);

            return new JsonResponse(
                ['message' => 'User created successfully'],
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }
}
