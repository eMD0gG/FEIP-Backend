<?php

namespace App\Tests\Service;

use App\DTO\CreateUserDto;
use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function testCreateUserSuccess(): void
    {
        $dto = new CreateUserDto('John Doe', substr('1234567890', 0, 13));

        $userRepo = $this->createMock(EntityRepository::class);
        $userRepo->method('findOneBy')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($userRepo);
        $em->expects($this->once())->method('persist');
        $em->expects($this->once())->method('flush');

        $service = new UserService($em);
        $service->createUser($dto);
    }

    public function testCreateUserAlreadyExists(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User with this number already exists');

        $dto = new CreateUserDto('John Doe', '1234567890');

        $existingUser = new User();

        $userRepo = $this->createMock(EntityRepository::class);
        $userRepo->method('findOneBy')->willReturn($existingUser);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($userRepo);

        $service = new UserService($em);
        $service->createUser($dto);
    }
}
