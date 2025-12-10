<?php

namespace App\Controller;

use App\DTO\CreateUserDto;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserController extends AbstractController
{   
    public function __construct(
        private UserService $userService 
    ) {}

    #[Route('/api/user', name: 'create_user', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        try {
            $content = $request->getContent();
            
            if (empty($content)) {
                throw new HttpException(400, 'Request body is empty');
            }

            $data = json_decode($content, true);

            if (!isset($data["name"]) || !isset($data["number"])) {
                throw new HttpException(400, 'Missing required fields: name and number');
            }

            $user = new CreateUserDto(
                $data["name"],
                $data["number"],
            );
            
            $this->userService->createUser($user);
            
            return new JsonResponse(
                ['message' => 'User created successfully'], 
                Response::HTTP_CREATED
            );

        } catch (HttpException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Internal server error'
            ], 500);
        }
    }
}