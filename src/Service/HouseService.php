<?php

namespace App\Service;

use App\DTO\CreateHouseDto;
use App\DTO\HouseDto;
use App\Entity\House;
use App\Repository\BookingRequestRepository;
use App\Repository\HouseRepository;
use Doctrine\ORM\EntityManagerInterface;

class HouseService
{
    public function __construct(
        private EntityManagerInterface $em,
        private HouseRepository $houseRepo,
        private BookingRequestRepository $bookingRepo,
    ) {
    }

    public function createHouse(CreateHouseDto $dto): House
    {
        $house = new House();
        $house->setArea($dto->area);
        $house->setAddress($dto->address);
        $house->setPrice($dto->price);
        $house->setBedrooms($dto->bedrooms);
        $house->setDistanceToSea($dto->distanceToSea);
        $house->setHasShower($dto->hasShower);
        $house->setHasBathroom($dto->hasBathroom);

        $this->em->persist($house);
        $this->em->flush();

        return $house;
    }

    public function findHouseById(int $id): ?House
    {
        return $this->houseRepo->find($id);
    }

    public function getAvailableHouses(): array
    {
        $allHouses = $this->houseRepo->findAll();
        $booked = $this->bookingRepo->findAll();

        $bookedIds = array_map(fn ($b) => $b->getHouse()->getId(), $booked);

        $available = array_filter($allHouses, fn (House $house) => !in_array($house->getId(), $bookedIds));

        return array_map(fn (House $h) => new HouseDto(
            id: $h->getId(),
            area: $h->getArea(),
            address: $h->getAddress(),
            price: $h->getPrice(),
            bedrooms: $h->getBedrooms(),
            distanceToSea: $h->getDistanceToSea(),
            hasShower: $h->hasShower(),
            hasBathroom: $h->hasBathroom()
        ), $available);
    }
}
