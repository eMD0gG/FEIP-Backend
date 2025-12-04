<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Controller\HouseController;
use App\Repository\HouseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HouseRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/houses/available',
            controller: HouseController::class . '::getAvailable',
            name: 'houses_available',
        ),
    ],
    paginationEnabled: false
)]
class House
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $area = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private ?int $bedrooms = null;

    #[ORM\Column]
    private ?int $distanceToSea = null;

    #[ORM\Column]
    private ?bool $hasShower = null;

    #[ORM\Column]
    private ?bool $hasBathroom = null;

    #[ORM\OneToMany(mappedBy: 'house', targetEntity: BookingRequest::class)]
    private $bookingRequests;

    public function __construct()
    {
        $this->bookingRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArea(): ?float
    {
        return $this->area;
    }

    public function setArea(?float $area): self
    {
        $this->area = $area;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getBedrooms(): ?int
    {
        return $this->bedrooms;
    }

    public function setBedrooms(?int $bedrooms): self
    {
        $this->bedrooms = $bedrooms;

        return $this;
    }

    public function getDistanceToSea(): ?int
    {
        return $this->distanceToSea;
    }

    public function setDistanceToSea(?int $distanceToSea): self
    {
        $this->distanceToSea = $distanceToSea;

        return $this;
    }

    public function hasShower(): ?bool
    {
        return $this->hasShower;
    }

    public function setHasShower(?bool $hasShower): self
    {
        $this->hasShower = $hasShower;

        return $this;
    }

    public function hasBathroom(): ?bool
    {
        return $this->hasBathroom;
    }

    public function setHasBathroom(?bool $hasBathroom): self
    {
        $this->hasBathroom = $hasBathroom;

        return $this;
    }

    /**
     * @return Collection<int, BookingRequest>
     */
    public function getBookingRequests(): Collection
    {
        return $this->bookingRequests;
    }

    public function addBookingRequest(BookingRequest $bookingRequest): static
    {
        if (!$this->bookingRequests->contains($bookingRequest)) {
            $this->bookingRequests->add($bookingRequest);
            $bookingRequest->setHouse($this);
        }

        return $this;
    }

    public function removeBookingRequest(BookingRequest $bookingRequest): static
    {
        if ($this->bookingRequests->removeElement($bookingRequest)) {
            // set the owning side to null (unless already changed)
            if ($bookingRequest->getHouse() === $this) {
                $bookingRequest->setHouse(null);
            }
        }

        return $this;
    }
}
