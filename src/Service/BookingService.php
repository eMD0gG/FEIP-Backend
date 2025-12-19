<?php

namespace App\Service;

use App\DTO\BookingDto;
use App\DTO\CreateBookingDto;
use App\DTO\UpdateBookingDto;
use App\Entity\BookingRequest;
use App\Entity\House;
use App\Entity\User;
use App\Repository\BookingRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

class BookingService
{
    public function __construct(
        private EntityManagerInterface $em,
        private BookingRequestRepository $bookingRepo,
    ) {
    }

    public function createBooking(CreateBookingDto $dto, User $user, House $house): BookingDto
    {
        $existingBooking = $this->bookingRepo->findOneBy(['house' => $house]);
        if ($existingBooking) {
            throw new \Exception('This house is already booked');
        }

        $booking = new BookingRequest();
        $booking->setUser($user);
        $booking->setHouse($house);
        $booking->setStatus('pending');
        $booking->setComment($dto->comment);

        $this->em->persist($booking);
        $this->em->flush();

        return new BookingDto(
            $booking->getId(),
            $user->getId(),
            $house->getId(),
            $booking->getStatus(),
            $booking->getComment()
        );
    }

    public function updateBooking(BookingRequest $booking, UpdateBookingDto $dto): BookingDto
    {
        if (null !== $dto->status) {
            $booking->setStatus($dto->status);
        }
        if (null !== $dto->comment) {
            $booking->setComment($dto->comment);
        }

        $this->em->flush();

        return new BookingDto(
            $booking->getId(),
            $booking->getUser()->getId(),
            $booking->getHouse()->getId(),
            $booking->getStatus(),
            $booking->getComment()
        );
    }
}
