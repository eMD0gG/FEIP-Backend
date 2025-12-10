<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;

class UserControllerTest extends WebTestCase
{
    private $em;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();

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

    public function testCreateUser(): void
    {
        $uniqueNumber = substr(str_replace('.', '', uniqid('number_', true)), 0, 13);

        $this->client->request(
            'POST',
            '/api/user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test User',
                'password' => '123',
                'number' => $uniqueNumber
            ])
        );

        $response = $this->client->getResponse();

        if ($response->getStatusCode() !== 201) {
            echo "Status Code: " . $response->getStatusCode() . PHP_EOL;
            echo "Response Content: " . $response->getContent() . PHP_EOL;
        }

        $this->assertEquals(201, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User created successfully', $responseData['message']);

        $user = $this->em->getRepository(User::class)->findOneBy(['number' => $uniqueNumber]);
        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user->getName());
    }
}
