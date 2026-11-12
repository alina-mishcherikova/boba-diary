<?php

namespace App\Tests\Controller;

use App\Entity\Store;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdminCrudControllerTest extends WebTestCase
{
    public function testStoreCreatorAndCreationForms(): void
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
            $store = (new Store())
                ->setName('Admin tea shop')
                ->setAddress('10 Rue de Paris')
                ->setCity('Paris')
                ->setCountry('France')
                ->setPostalCode('75001')
                ->setCreatedAt($now)
                ->setCreatedBy($user);
            $entityManager->persist($store);
            $entityManager->flush();
            $client->loginUser($user);

            $client->request('GET', '/admin/store');
            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('tbody', 'Admin tea shop');
            self::assertSelectorTextContains('td[data-column="created_by"]', 'creator@example.com');

            $client->request('GET', '/admin/store/new');
            self::assertResponseIsSuccessful();
            self::assertSelectorExists('form input[name="Store[name]"]');
            self::assertSelectorNotExists('[name="Store[createdBy]"]');

            $client->request('GET', '/admin/bubble-tea/new');
            self::assertResponseIsSuccessful();
            self::assertSelectorExists('form input[name="BubbleTea[name]"]');
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
