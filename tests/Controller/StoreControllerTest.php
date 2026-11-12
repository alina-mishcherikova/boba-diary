<?php

namespace App\Tests\Controller;

use App\Entity\Store;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class StoreControllerTest extends WebTestCase
{
    public function testCreateStore(): void
    {
        $databasePath = tempnam(sys_get_temp_dir(), 'store-test-');
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
            $client->loginUser($user);
            $crawler = $client->request('GET', '/store');

            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('.store-form h1', 'Create a bubble tea store');
            self::assertSelectorCount(1, '.store-form form[name="store"] .rows');
            foreach (['name', 'address', 'city', 'country', 'postal_code'] as $field) {
                self::assertSelectorExists('.store-form .rows .form-input input[name="store['.$field.']"]');
            }
            self::assertSelectorExists('.store-form button.btn-full[type="submit"]');

            $client->submit($crawler->selectButton('Save')->form([
                'store[name]' => 'Tea shop',
                'store[address]' => '10 Rue de Paris',
                'store[city]' => 'Paris',
                'store[country]' => 'France',
                'store[postal_code]' => '75001',
            ]));

            self::assertResponseRedirects('/profile');
            $saved = static::getContainer()->get(EntityManagerInterface::class)
                ->getRepository(Store::class)->findOneBy(['name' => 'Tea shop']);
            self::assertNotNull($saved);
            self::assertSame('Paris', $saved->getCity());
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
