<?php

namespace App\Service;

use App\DTO\CreateUserDto;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function createUser(CreateUserDto $dto)
    {
        $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['number' => $dto->number]);

        if ($existingUser) {
            throw new \Exception('User with this number already exists');
        }

        $user = new User();
        $user->setNumber($dto->number);
        $user->setName($dto->name);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
