<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\AccessToken;
use App\Entity\User;
use App\Repository\AccessTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private readonly AccessTokenRepository $accessTokenRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function createToken(User $user, ?\DateInterval $ttl = null): AccessToken
    {
        $this->accessTokenRepository->removeTokensByUser($user);

        $token = new AccessToken();
        $token->setToken($this->generateToken());
        $token->setUser($user);

        $expiresIn = $ttl ?? new \DateInterval('P7D');

        $token->setExpiresAt(
            (new \DateTimeImmutable('now'))->add($expiresIn)
        );

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $token = $this->getValidTokenOrFail($accessToken);
        $user = $token->getUser();

        return new UserBadge($user->getUserIdentifier(), fn() => $user);
    }

    public function findToken(string $token): ?AccessToken
    {
        return $this->accessTokenRepository->findOneBy(['token' => $token]);
    }

    private function generateToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
    }

    private function getValidTokenOrFail(string $token): AccessToken
    {
        $accessToken = $this->accessTokenRepository->findOneBy(['token' => $token]);

        if (!$accessToken || $accessToken->isExpired()) {
            throw new AuthenticationException('Invalid or expired access token.');
        }

        return $accessToken;
    }
}
