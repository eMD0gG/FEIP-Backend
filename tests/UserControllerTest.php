<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;

class UserControllerTest extends WebTestCase
{
    private $em;

    private function createSchema(): void
    {
        $metaData = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($metaData)) {
            $schemaTool = new SchemaTool($this->em);
            $schemaTool->dropSchema($metaData);
            $schemaTool->createSchema($metaData);
        }
    }

    public function testCreateUser(): void
    {
        $client = static::createClient();
        $this->em = $client->getContainer()->get('doctrine')->getManager();
        $this->createSchema();

        $client->request(
            'POST',
            '/api/user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test User',
                'number' => uniqid('number_')
            ])
        );

        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User created successfully', $responseData['message']);
    }
}
