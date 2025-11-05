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
        $availableHouses = $this->houseService->getAvailableHouses();
        return $this->json($availableHouses);
    }

    #[Route('/book', methods: ['POST'])]
    public function book(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
         if (empty($data)) {
            return $this->json(['error' => 'Request body is empty'], 400);
        }
        
        if (empty($data['user_id']) || empty($data['house_id'])) {
            return $this->json(['error' => 'user_id and house_id are required'], 400);
        }

        $user = $this->userRepo->find($data['user_id']);
        $house = $this->houseRepo->find($data['house_id']);

        if (!$user || !$house) {
            return $this->json(['error' => 'User or house not found'], 404);
        }

        try {
            $bookingDto = $this->bookingService->createBooking(
                new CreateBookingDto($data['comment'] ?? null),
                $user,
                $house
            );
            
            return $this->json([
                'message' => 'Booking created successfully',
                'booking' => $bookingDto
            ], 201);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 409);
        }
    }

    #[Route('/book/{id}', methods: ['PUT'])]
    public function updateBooking(int $id, Request $request): JsonResponse
    {
        $booking = $this->bookingRepo->find($id);

        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

         $allowedStatuses = ['pending', 'confirmed', 'cancelled'];
        if (isset($data['status']) && !in_array($data['status'], $allowedStatuses)) {
            return $this->json(['error' => 'Invalid status'], 400);
        }

         try {
            $updateDto = new UpdateBookingDto(
                $data['status'] ?? null,
                $data['comment'] ?? null
            );
            
            $bookingDto = $this->bookingService->updateBooking($booking, $updateDto);
            
            return $this->json([
                'message' => 'Booking updated successfully',
                'booking' => $bookingDto
            ]);
            
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }
}