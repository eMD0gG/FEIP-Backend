<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\Tools\SchemaTool;
use App\Entity\User;

class UserControllerTest extends WebTestCase
{
    private $em;

    /**
     * Создаёт/сбрасывает базу данных перед тестом
     */
    private function createSchema(): void
    {
        $metaData = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($metaData)) {
            $schemaTool = new SchemaTool($this->em);
            try {
                $schemaTool->dropSchema($metaData);
                $schemaTool->createSchema($metaData);
            } catch (\Exception $e) {
                echo "Ошибка при создании схемы: " . $e->getMessage() . PHP_EOL;
                throw $e;
            }
        }
    }

    public function testCreateUser(): void
    {
        $client = static::createClient();

        // Получаем EntityManager
        $this->em = $client->getContainer()->get('doctrine')->getManager();

        // Создаём схему базы данных
        $this->createSchema();

        // Генерируем уникальный номер длиной ровно 13 символов
        $uniqueNumber = substr(str_replace('.', '', uniqid('number_', true)), 0, 13);

        // Отправляем POST-запрос
        $client->request(
            'POST',
            '/api/user',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test User',
                'number' => $uniqueNumber
            ])
        );

        // Получаем ответ
        $response = $client->getResponse();

        // Для дебага (показывает реальную ошибку в CI)
        if ($response->getStatusCode() !== 201) {
            echo "Ответ сервера: " . $response->getContent() . PHP_EOL;
        }

        // Проверяем код ответа
        $this->assertEquals(201, $response->getStatusCode());

        // Проверяем содержимое ответа
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User created successfully', $responseData['message']);

        // Дополнительно: проверяем, что пользователь реально создался в базе
        $user = $this->em->getRepository(User::class)->findOneBy(['number' => $uniqueNumber]);
        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user->getName());
    }
}
