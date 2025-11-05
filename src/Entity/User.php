<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column(length: 13, unique: true)]
    private string $number;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: BookingRequest::class)]
    private $bookingRequests;

    public function __construct()
    {
        $this->bookingRequests = new ArrayCollection();
    }

    public function getId(): ?int 
    { 
        return $this->id; 
    }

    public function getName(): string 
    { 
        return $this->name; 
    }

    public function setName(string $name): self 
    { 
        $this->name = $name; 
        return $this; 
    }

    public function getNumber(): string 
    { 
        return $this->number; 
    }

    public function setNumber(string $number): self 
    {
        $this->number = $number; 
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
            $bookingRequest->setUser($this);
        }

        return $this;
    }

    public function removeBookingRequest(BookingRequest $bookingRequest): static
    {
        if ($this->bookingRequests->removeElement($bookingRequest)) {
            if ($bookingRequest->getUser() === $this) {
                $bookingRequest->setUser(null);
            }
        }

        return $this;
    }
}
