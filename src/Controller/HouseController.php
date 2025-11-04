<?php
namespace App\Controller;

use App\Service\CsvService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/houses')]
class HouseController extends AbstractController
{
    private CsvService $csvService;

    public function __construct(CsvService $csvService)
    {
        $this->csvService = $csvService;
    }

    #[Route('/available', methods: ['GET'])]
    public function getAvailable(): JsonResponse
    {
        $houses = $this->csvService->readCsv('houses.csv');
        $bookings = $this->csvService->readCsv('bookings.csv');

        $bookedIds = array_column($bookings, 'house_id');

        $available = array_filter($houses, function ($house) use ($bookedIds) {
        return !in_array($house['id'], $bookedIds);
        });

        return new JsonResponse($available);
    }

    #[Route('/book', methods: ['POST'])]
    public function book(Request $request): JsonResponse
    {
        $bookings = $this->csvService->readCsv('bookings.csv');
        $data = json_decode($request->getContent(), true);

        $newBooking = [
            'id' => count($bookings) + 1,
            'house_id' => $data['cottage_id'],
            'phone' => $data['phone'],
            'comment' => $data['comment'] ?? ''
        ];

        $bookings[] = $newBooking;
        $this->csvService->writeCsv('bookings.csv', $bookings, ['id', 'cottage_id', 'phone', 'comment']);

        return new JsonResponse(['booking' => $newBooking], status: 200);
    }

    #[Route('/book/{id}', methods: ['PUT'])]
    public function updateBooking(int $id, Request $request): JsonResponse
    {
        $bookings = $this->csvService->readCsv('bookings.csv');
        $data = json_decode($request->getContent(), true);
        $updated = false;

        foreach ($bookings as &$booking) {
            if ($booking['id'] == $id) {
                $booking['comment'] = $data['comment'] ?? '';
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->csvService->writeCsv('bookings.csv', $bookings, ['id', 'cottage_id', 'phone', 'comment']);
            return new JsonResponse(status: 204);
        }

        return new JsonResponse(status: 404);
    }

}