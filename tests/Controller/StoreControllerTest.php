<?php

namespace App\Tests\Controller;

use App\Entity\BubbleTea;
use App\Entity\Currency;
use App\Entity\Store;
use App\Entity\StoreBubbleTea;
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
                ->setRoles(['ROLE_OWNER'])
                ->setPassword('unused-in-this-test')
                ->setUsername('creator')
                ->setName('Creator')
                ->setCreatedAt($now)
                ->setCguValidatedAt($now);
            $currency = (new Currency())->setCode('EUR')->setName('Euro')->setSymbol('€')->setIsActive(true);
            $entityManager->persist($currency);
            $entityManager->persist($user);
            $entityManager->flush();
            $currencyId = $currency->getId();
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
                'store[currency]' => (string) $currencyId,
            ]));

            self::assertResponseRedirects('/my-locations');
            $saved = static::getContainer()->get(EntityManagerInterface::class)
                ->getRepository(Store::class)->findOneBy(['name' => 'Tea shop']);
            self::assertNotNull($saved);
            self::assertSame('Paris', $saved->getCity());
            self::assertSame('EUR', $saved->getCurrency()->getCode());
            $client->followRedirect();
            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('main', 'Tea shop');

            $catalog = $client->request('GET', '/explore');
            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('.explore-card', '10 Rue de Paris, Paris, France');
            $client->click($catalog->selectLink('Tea shop')->link());
            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('h1', 'Tea shop');
            self::assertSelectorTextContains('.explore-menu', 'No drinks on this menu yet.');

            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            $drink = (new BubbleTea())->setName('Jasmine tea')->setDescription('Jasmine and pearls')
                ->setCreatedAt($now)->setCreatedBy($entityManager->getReference(User::class, $user->getId()));
            $item = (new StoreBubbleTea())->setBubbleTea($drink)->setPrice('5.50')
                ->setCreatedAt($now)->setIsAvailable(true)
                ->setStoreId($entityManager->getReference(Store::class, $saved->getId()));
            $entityManager->persist($drink);
            $entityManager->persist($item);
            $entityManager->flush();

            $client->request('GET', '/explore/drinks');
            self::assertResponseIsSuccessful();
            self::assertSelectorTextContains('[aria-label="Explore catalog"] [aria-current]', 'Drinks');
            self::assertSelectorTextContains('.explore-card', 'Jasmine tea');
            self::assertSelectorTextContains('.explore-card', 'From 5.50 EUR');

            $catalog = $client->request('GET', '/explore');
            $client->click($catalog->selectLink('Tea shop')->link());
            self::assertSelectorTextContains('.explore-menu', 'Jasmine tea');
            $catalog = $client->request('GET', '/explore');
            $client->click($catalog->selectLink('Suggest a store')->link());
            self::assertSelectorTextContains('.store-form h1', 'Create a bubble tea store');
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
