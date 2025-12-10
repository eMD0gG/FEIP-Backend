<?php

namespace App\Repository;

use App\Entity\AccessToken;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AccessTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccessToken::class);
    }

    public function findValidToken(string $token): ?AccessToken
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.token = :token')
            ->andWhere('t.expiresAt > :now')
            ->setParameter('token', $token)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findToken(string $token): ?AccessToken
    {
        return $this->findOneBy(['token' => $token]);
    }

    public function removeTokensByUser(User $user): void
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete(AccessToken::class, 't')
           ->where('t.user = :user')
           ->setParameter('user', $user)
           ->getQuery()
           ->execute();
    }
}
