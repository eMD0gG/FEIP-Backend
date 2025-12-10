<?php

namespace App\Tests\Service;

use App\DTO\BookingDto;
use App\DTO\CreateBookingDto;
use App\DTO\UpdateBookingDto;
use App\Entity\BookingRequest;
use App\Entity\House;
use App\Entity\User;
use App\Service\BookingService;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookingRequestRepository;
use PHPUnit\Framework\TestCase;

class BookingServiceTest extends TestCase
{
    private $em;
    private $bookingRepo;
    private $service;

    protected function setUp(): void
    {
        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->bookingRepo = $this->createMock(BookingRequestRepository::class);

        $this->service = new BookingService(
            $this->em,
            $this->bookingRepo
        );
    }

    public function testCreateBookingSuccess(): void
    {
        $user = new User();
        $house = new House();

        $dto = new CreateBookingDto('Test comment');

        $this->bookingRepo->method('findOneBy')->willReturn(null);

        $this->em->expects($this->once())->method('persist');
        $this->em->expects($this->once())->method('flush');

        $result = $this->service->createBooking($dto, $user, $house);

        $this->assertInstanceOf(BookingDto::class, $result);
        $this->assertEquals('pending', $result->status);
        $this->assertEquals('Test comment', $result->comment);
    }

    public function testCreateBookingThrowsIfAlreadyBooked(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This house is already booked');

        $user = new User();
        $house = new House();
        $dto = new CreateBookingDto('Test comment');

        $existingBooking = new BookingRequest();
        $this->bookingRepo->method('findOneBy')->willReturn($existingBooking);

        $this->service->createBooking($dto, $user, $house);
    }

    public function testUpdateBookingSuccess(): void
    {
        $booking = new BookingRequest();
        $user = new User();
        $house = new House();

        $booking->setUser($user);
        $booking->setHouse($house);
        $booking->setStatus('pending');
        $booking->setComment('Old comment');

        $dto = new UpdateBookingDto('confirmed', 'New comment');

        $this->em->expects($this->once())->method('flush');

        $result = $this->service->updateBooking($booking, $dto);

        $this->assertInstanceOf(BookingDto::class, $result);
        $this->assertEquals('confirmed', $result->status);
        $this->assertEquals('New comment', $result->comment);
    }
}
