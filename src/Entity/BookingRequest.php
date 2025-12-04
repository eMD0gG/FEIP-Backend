<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\HouseController;
use App\Repository\BookingRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRequestRepository::class)]
#[ApiResource(
    operations: [

        new Post(
            uriTemplate: '/houses/book',
            controller: HouseController::class . '::book',
            denormalizationContext: ['groups' => ['booking:write']],
            normalizationContext: ['groups' => ['booking:read']],
            name: 'create_booking'
        ),

        new Put(
            uriTemplate: '/houses/book/{id}',
            controller: HouseController::class . '::updateBooking',
            denormalizationContext: ['groups' => ['booking:update']],
            normalizationContext: ['groups' => ['booking:read']],
            name: 'update_booking'
        ),
    ],
    paginationEnabled: false
)]
class BookingRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookingRequests', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'bookingRequests', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?House $house = null;

    #[ORM\Column(length: 50)]
    private string $status = 'pending';

    #[ORM\Column(length: 100)]
    private ?string $comment = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getHouse(): ?House
    {
        return $this->house;
    }

    public function setHouse(?House $house): self
    {
        $this->house = $house;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getComment(): string
    {
        return $this->comment  ?? '';
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
