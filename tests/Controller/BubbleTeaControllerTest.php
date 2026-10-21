<?php

namespace App\Tests\Controller;

use App\Entity\BubbleTea;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BubbleTeaControllerTest extends WebTestCase
{
    public function testCreateBubbleTea(): void
    {
        $databasePath = tempnam(sys_get_temp_dir(), 'bubble-tea-test-');
        $previousEnv = $_ENV['DATABASE_URL'] ?? null;
        $previousServer = $_SERVER['DATABASE_URL'] ?? null;
        $_ENV['DATABASE_URL'] = $_SERVER['DATABASE_URL'] = 'sqlite:///'.$databasePath;

        try {
            $client = static::createClient();
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
            (new SchemaTool($entityManager))->createSchema($metadata);

            $now = new \DateTimeImmutable();
            $user = (new User())
                ->setEmail('creator@example.com')
                ->setPassword('unused-in-this-test')
                ->setUsername('creator')
                ->setName('Creator')
                ->setCreatedAt($now)
                ->setCguValidatedAt($now);
            $entityManager->persist($user);
            $entityManager->flush();
            $userId = $user->getId();

            $client->request('GET', '/bubble/tea');
            self::assertResponseRedirects('/login');
            $client->loginUser($user);

            $crawler = $client->request('GET', '/bubble/tea');
            self::assertResponseIsSuccessful();
            self::assertSelectorExists('form button[type="submit"]');
            foreach (['image_url', 'created_at', 'inactivated_at', 'created_by'] as $field) {
                self::assertSelectorNotExists('[name="bubble_tea['.$field.']"]');
            }

            $beforeSubmission = new \DateTimeImmutable();
            $form = $crawler->selectButton('Save')->form([
                'bubble_tea[name]' => 'Jasmine milk tea',
                'bubble_tea[description]' => 'With tapioca pearls',
            ]);
            $client->submit($form);

            self::assertResponseRedirects('/bubble/tea');
            $saved = static::getContainer()->get(EntityManagerInterface::class)
                ->getRepository(BubbleTea::class)->findOneBy(['name' => 'Jasmine milk tea']);
            self::assertNotNull($saved);
            self::assertSame('With tapioca pearls', $saved->getDescription());
            self::assertNull($saved->getImageUrl());
            self::assertNull($saved->getInactivatedAt());
            self::assertSame($userId, $saved->getCreatedBy()->getId());
            self::assertNotNull($saved->getCreatedAt());
            self::assertGreaterThanOrEqual($beforeSubmission->getTimestamp(), $saved->getCreatedAt()->getTimestamp());
            self::assertLessThanOrEqual(time(), $saved->getCreatedAt()->getTimestamp());

            $client->followRedirect();
            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('p', 'Bubble tea created!');
        } finally {
            static::ensureKernelShutdown();
            if (null === $previousEnv) {
                unset($_ENV['DATABASE_URL']);
            } else {
                $_ENV['DATABASE_URL'] = $previousEnv;
            }
            if (null === $previousServer) {
                unset($_SERVER['DATABASE_URL']);
            } else {
                $_SERVER['DATABASE_URL'] = $previousServer;
            }
            unlink($databasePath);
        }
    }
}
