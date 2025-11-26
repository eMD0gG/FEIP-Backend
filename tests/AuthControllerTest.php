<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\AccessToken;
use App\Security\AccessTokenHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AuthControllerTest extends WebTestCase
{
    private $client;
    private $em;
    private $tokenHandler;
    private $passwordHasher;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get(EntityManagerInterface::class);
        $this->tokenHandler = $this->createMock(AccessTokenHandler::class);
        $this->passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);

        $this->em->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->em->getConnection()->isTransactionActive()) {
            $this->em->getConnection()->rollback();
        }

        $this->em->close();
        $this->em = null;
        $this->client = null;

        parent::tearDown();
    }

    private function createTestUser(string $number = '+1234567890', string $password = 'password'): User
    {
        $user = new User();
        $user->setNumber($number);
        $user->setName('Test User');
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function createTestAccessToken(User $user): AccessToken
    {
        $token = new AccessToken();
        $token->setToken('test_token_123');
        $token->setUser($user);
        $token->setExpiresAt(new \DateTimeImmutable('+7 days'));

        $this->em->persist($token);
        $this->em->flush();

        return $token;
    }

    public function testLoginSuccess(): void
    {
        $user = $this->createTestUser('+1234567890', 'password123');
        
        $expectedToken = new AccessToken();
        $expectedToken->setToken('generated_token_123');
        $expectedToken->setExpiresAt(new \DateTimeImmutable('+7 days'));

        $this->tokenHandler->expects($this->once())
            ->method('createToken')
            ->with($user)
            ->willReturn($expectedToken);

        $this->client->getContainer()->set(AccessTokenHandler::class, $this->tokenHandler);

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'number' => '+1234567890',
                'password' => 'password123'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertArrayHasKey('expires_at', $responseData);
        $this->assertEquals('generated_token_123', $responseData['token']);
    }

    public function testLoginWithMissingCredentials(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['number' => '+1234567890'])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Phone and password required', $responseData['error']);
    }

    public function testLoginWithInvalidPhone(): void
    {
        $this->createTestUser('+1234567890', 'password123');

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'number' => '+0987654321',
                'password' => 'password123'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Invalid credentials', $responseData['error']);
    }

    public function testLoginWithInvalidPassword(): void
    {
        $this->createTestUser('+1234567890', 'correct_password');

        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'number' => '+1234567890',
                'password' => 'wrong_password'
            ])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Invalid credentials', $responseData['error']);
    }

    public function testLogoutSuccess(): void
    {
        $user = $this->createTestUser();
        $accessToken = $this->createTestAccessToken($user);

        $this->tokenHandler->expects($this->once())
            ->method('findToken')
            ->with('test_token_123')
            ->willReturn($accessToken);

        $this->client->getContainer()->set(AccessTokenHandler::class, $this->tokenHandler);

        $this->client->request(
            'POST',
            '/api/logout',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer test_token_123'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertTrue($responseData['success']);

        $tokenAfterLogout = $this->em->getRepository(AccessToken::class)
            ->findOneBy(['token' => 'test_token_123']);
        $this->assertNull($tokenAfterLogout);
    }

    public function testLogoutWithoutAuthorizationHeader(): void
    {
        $this->client->request(
            'POST',
            '/api/logout',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json']
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Authorization header missing', $responseData['error']);
    }

    public function testLogoutWithInvalidTokenFormat(): void
    {
        $this->client->request(
            'POST',
            '/api/logout',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'InvalidFormat test_token_123'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Authorization header missing', $responseData['error']);
    }

    public function testLogoutWithNonExistentToken(): void
    {
        $this->tokenHandler->expects($this->once())
            ->method('findToken')
            ->with('non_existent_token')
            ->willReturn(null);

        $this->client->getContainer()->set(AccessTokenHandler::class, $this->tokenHandler);

        $this->client->request(
            'POST',
            '/api/logout',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer non_existent_token'
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Token not found or already invalidated', $responseData['error']);
    }

    public function testLoginWithMalformedJSON(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'malformed{json'
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testLoginWithEmptyRequestBody(): void
    {
        $this->client->request(
            'POST',
            '/api/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{}'
        );

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Phone and password required', $responseData['error']);
    }
}