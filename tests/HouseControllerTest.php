<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\User;
use App\Entity\House;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

class HouseControllerTest extends WebTestCase
{
    private EntityManagerInterface $em;

    private function getEntityManager($client): EntityManagerInterface
    {
        return $client->getContainer()->get('doctrine')->getManager();
    }

    private function createSchema(): void
    {
        $metaData = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($metaData)) {
            $schemaTool = new SchemaTool($this->em);
            $schemaTool->dropSchema($metaData);
            $schemaTool->createSchema($metaData);
        }
    }

    private function createTestUser(): User
    {
        $user = new User();
        $user->setName('Test User');
        $user->setNumber(substr(uniqid('user_'), 0, 13));
        $user->setPassword(password_hash('223432', PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_ADMIN_ACCESS']);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function createTestHouse(): House
    {
        $house = new House();
        $house->setArea(120);
        $house->setAddress('Test Address');
        $house->setPrice(100000);
        $house->setBedrooms(3);
        $house->setDistanceToSea(500);
        $house->setHasShower(true);
        $house->setHasBathroom(true);

        $this->em->persist($house);
        $this->em->flush();

        return $house;
    }

    public function testGetAvailableHouses(): void
    {
        $client = static::createClient();
        $this->em = $this->getEntityManager($client);

        $this->createSchema();

        $client->request('GET', '/api/houses/available');

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
    }

    public function testBookHouse(): void
    {
        $client = static::createClient();
        $this->em = $this->getEntityManager($client);

        $this->createSchema();

        $user = $this->createTestUser();

        $client->loginUser($user);
        
        $house = $this->createTestHouse();

        $client->request(
            'POST',
            '/api/houses/book',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'user_id' => $user->getId(),
                'house_id' => $house->getId(),
                'comment' => 'Test booking'
            ])
        );

        $response = $client->getResponse();
        $this->assertEquals(201, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Booking created successfully', $responseData['message']);
        $this->assertArrayHasKey('booking', $responseData);
        $this->assertArrayHasKey('id', $responseData['booking']);
        $this->assertArrayHasKey('userId', $responseData['booking']);
        $this->assertArrayHasKey('houseId', $responseData['booking']);
        $this->assertArrayHasKey('status', $responseData['booking']);
        $this->assertArrayHasKey('comment', $responseData['booking']);
    }
}
