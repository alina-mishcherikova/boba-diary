<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Every bubble tea you’ve ever loved, in one place.');
        self::assertSelectorExists('header a[href="/login"]');
        self::assertSelectorExists('main a[href="#inside-the-app"]');
        self::assertSelectorCount(4, '#inside-the-app article');
        self::assertSelectorExists('#signup form[method="post"][action="/#signup"]');
    }

    public function testRegistrationFromHome(): void
    {
        $databasePath = tempnam(sys_get_temp_dir(), 'home-registration-');
        self::assertNotFalse($databasePath);
        $previousEnv = $_ENV['DATABASE_URL'] ?? null;
        $previousServer = $_SERVER['DATABASE_URL'] ?? null;
        $_ENV['DATABASE_URL'] = $_SERVER['DATABASE_URL'] = 'sqlite:///'.$databasePath;

        try {
            $client = static::createClient();
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            (new SchemaTool($entityManager))->createSchema([$entityManager->getClassMetadata(User::class)]);

            $crawler = $client->request('GET', '/');
            $fields = [
                'registration_form[name]' => 'Mira Halloway',
                'registration_form[username]' => 'mirah',
                'registration_form[email]' => 'mira@example.com',
                'registration_form[plainPassword]' => 'bubble-tea-123',
            ];
            $crawler = $client->submit($crawler->selectButton('Create account')->form($fields));

            self::assertResponseStatusCodeSame(422);
            self::assertSelectorTextContains('#signup form', 'You should agree to our terms.');

            $client->submit($crawler->selectButton('Create account')->form($fields + ['registration_form[agreeTerms]' => true]));
            self::assertResponseRedirects('/profile');

            $client->request('GET', '/');
            self::assertResponseIsSuccessful();
            self::assertSelectorExists('#signup a[href="/profile"]');
            self::assertSelectorNotExists('#signup form');
        } finally {
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
