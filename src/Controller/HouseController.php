<?php
namespace App\Controller;

use App\Dto\CreateBookingDto;
use App\Dto\UpdateBookingDto;
use App\Service\HouseService;
use App\Service\BookingService;
use App\Repository\HouseRepository;
use App\Repository\BookingRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/houses')]
class HouseController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private HouseRepository $houseRepo,
        private BookingRequestRepository $bookingRepo,
        private UserRepository $userRepo,
        private HouseService $houseService,
        private BookingService $bookingService
    ) {}

    #[Route('/available', methods: ['GET'])]
    public function getAvailable(): JsonResponse
    {
        try {
            $availableHouses = $this->houseService->getAvailableHouses();
            
            return $this->json([
                'success' => true,
                'data' => $availableHouses
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/book', methods: ['POST'])]
    public function book(Request $request): JsonResponse
    {
        try {
            $content = $request->getContent();
            
            if (empty($content)) {
                throw new HttpException(400, 'Request body is empty');
            }

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new HttpException(400, 'Invalid JSON: ' . json_last_error_msg());
            }

            if (empty($data['user_id']) || empty($data['house_id'])) {
                throw new HttpException(400, 'user_id and house_id are required');
            }

            $user = $this->userRepo->find($data['user_id']);
            $house = $this->houseRepo->find($data['house_id']);

            if (!$user) {
                throw new HttpException(404, 'User not found');
            }

            if (!$house) {
                throw new HttpException(404, 'House not found');
            }

            $bookingDto = $this->bookingService->createBooking(
                new CreateBookingDto($data['comment'] ?? null),
                $user,
                $house
            );
            
            return $this->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'booking' => $bookingDto
            ], 201);
            
        } catch (HttpException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 409);
        }
    }

    #[Route('/book/{id}', methods: ['PUT'])]
    public function updateBooking(int $id, Request $request): JsonResponse
    {
        try {
            $booking = $this->bookingRepo->find($id);

            if (!$booking) {
                throw new HttpException(404, 'Booking not found');
            }

            $content = $request->getContent();
            
            if (empty($content)) {
                throw new HttpException(400, 'Request body is empty');
            }

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new HttpException(400, 'Invalid JSON: ' . json_last_error_msg());
            }

            $allowedStatuses = ['pending', 'confirmed', 'cancelled'];
            if (isset($data['status']) && !in_array($data['status'], $allowedStatuses)) {
                throw new HttpException(400, 'Invalid status');
            }

            $updateDto = new UpdateBookingDto(
                $data['status'] ?? null,
                $data['comment'] ?? null
            );
            
            $bookingDto = $this->bookingService->updateBooking($booking, $updateDto);
            
            return $this->json([
                'success' => true,
                'message' => 'Booking updated successfully',
                'booking' => $bookingDto
            ]);
            
        } catch (HttpException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}