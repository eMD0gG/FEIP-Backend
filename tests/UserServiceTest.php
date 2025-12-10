<?php

namespace App\Tests\Service;

use App\DTO\CreateUserDto;
use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends TestCase
{
    public function testCreateUserSuccess(): void
    {
        $dto = new CreateUserDto('John Doe', '123', substr('1234567890', 0, 13));

        $userRepo = $this->createMock(EntityRepository::class);
        $userRepo->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($userRepo);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed_password');

        $service = new UserService($em, $hasher);
        $service->createUser($dto);
    }

    public function testCreateUserAlreadyExists(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User with this number already exists');

        $dto = new CreateUserDto('John Doe', '123', '1234567890');

        $existingUser = new User();

        $userRepo = $this->createMock(EntityRepository::class);
        $userRepo->method('findOneBy')->willReturn($existingUser);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($userRepo);

        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->method('hashPassword')->willReturn('hashed_password');

        $service = new UserService($em, $hasher);
        $service->createUser($dto);
    }
}
