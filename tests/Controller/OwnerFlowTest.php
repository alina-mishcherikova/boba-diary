<?php

namespace App\Tests\Controller;

use App\Entity\BubbleTea;
use App\Entity\Currency;
use App\Entity\Store;
use App\Entity\StoreBubbleTea;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OwnerFlowTest extends WebTestCase
{
    private string $databasePath;
    private ?string $previousEnv;
    private ?string $previousServer;
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->previousEnv = $_ENV['DATABASE_URL'] ?? null;
        $this->previousServer = $_SERVER['DATABASE_URL'] ?? null;
        $this->databasePath = tempnam(sys_get_temp_dir(), 'owner-flow-');
        $_ENV['DATABASE_URL'] = $_SERVER['DATABASE_URL'] = 'sqlite:///'.$this->databasePath;
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        (new SchemaTool($this->entityManager))->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        foreach (['previousEnv' => '_ENV', 'previousServer' => '_SERVER'] as $property => $global) {
            if (null === $this->$property) {
                unset($GLOBALS[$global]['DATABASE_URL']);
            } else {
                $GLOBALS[$global]['DATABASE_URL'] = $this->$property;
            }
        }
        unlink($this->databasePath);
    }

    public static function registrationRoles(): iterable
    {
        yield 'regular user' => [false];
        yield 'location owner' => [true];
    }

    #[DataProvider('registrationRoles')]
    public function testRegistrationAndNavigation(bool $isOwner): void
    {
        $crawler = $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('input[name="registration_form[isOwner]"]:not([required])');
        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => 'new@example.com',
            'registration_form[username]' => 'newuser',
            'registration_form[name]' => 'New User',
            'registration_form[plainPassword]' => 'Password123!',
            'registration_form[agreeTerms]' => true,
            'registration_form[isOwner]' => $isOwner,
        ]);
        $this->client->submit($form);
        self::assertResponseRedirects('/profile');
        $user = static::getContainer()->get(EntityManagerInterface::class)->getRepository(User::class)->findOneBy(['email' => 'new@example.com']);
        self::assertNotNull($user);
        self::assertContains('ROLE_USER', $user->getRoles());
        self::assertSame($isOwner, in_array('ROLE_OWNER', $user->getRoles(), true));
        self::assertNotContains('ROLE_ADMIN', $user->getRoles());

        $crawler = $this->client->request('GET', '/login');
        $this->client->submit($crawler->selectButton('Sign in')->form([
            'email' => 'new@example.com',
            'password' => 'Password123!',
        ]));
        self::assertResponseRedirects('/profile');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        if ($isOwner) {
            self::assertSelectorTextContains('nav a[href="/my-locations"]', 'My locations');
        } else {
            self::assertSelectorNotExists('nav a[href="/my-locations"]');
        }
        $this->client->request('GET', '/my-locations');
        self::assertResponseStatusCodeSame($isOwner ? 200 : 403);
        $this->client->request('GET', '/store');
        self::assertResponseStatusCodeSame($isOwner ? 200 : 403);
    }

    public function testOwnerCanAddDrinkOnlyToOwnLocation(): void
    {
        $owner = $this->user('owner@example.com', ['ROLE_OWNER']);
        $other = $this->user('other@example.com', ['ROLE_OWNER']);
        $ownStore = $this->store('Own location', $owner);
        $otherStore = $this->store('Other location', $other);
        $drink = (new BubbleTea())->setName('Matcha Latte')->setCreatedBy($other)->setCreatedAt(new \DateTimeImmutable());
        $currency = (new Currency())->setCode('EUR')->setName('Euro')->setSymbol('€')->setIsActive(true);
        $this->entityManager->persist($drink);
        $this->entityManager->persist($currency);
        $ownStore->setCurrency($currency);
        $this->entityManager->flush();
        $ownId = $ownStore->getId();
        $otherId = $otherStore->getId();
        $drinkId = $drink->getId();
        $this->client->loginUser($owner);

        $this->client->request('GET', '/my-locations');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Own location');
        self::assertSelectorTextNotContains('main', 'Other location');

        $this->client->request('GET', '/my-locations/'.$otherId.'/menu');
        self::assertResponseStatusCodeSame(403);
        $this->client->request('POST', '/my-locations/'.$otherId.'/menu');
        self::assertResponseStatusCodeSame(403);

        $crawler = $this->client->request('GET', '/my-locations/'.$ownId.'/menu');
        self::assertResponseIsSuccessful();
        self::assertSelectorNotExists('[name="store_menu_item[store_id]"]');
        self::assertSelectorNotExists('[name="store_menu_item[currency]"]');
        $crawler = $this->client->submit($crawler->selectButton('Add to menu')->form([
            'store_menu_item[bubbleTea]' => (string) $drinkId,
            'store_menu_item[price]' => '-1.00',
        ]));
        self::assertSelectorTextContains('form', 'This value should be either positive or zero.');
        self::assertSame(0, static::getContainer()->get(EntityManagerInterface::class)->getRepository(StoreBubbleTea::class)->count([]));

        $this->client->submit($crawler->selectButton('Add to menu')->form([
            'store_menu_item[bubbleTea]' => (string) $drinkId,
            'store_menu_item[price]' => '6.50',
            'store_menu_item[is_available]' => true,
        ]));
        self::assertResponseRedirects('/my-locations/'.$ownId.'/menu');
        $crawler = $this->client->followRedirect();
        self::assertSelectorTextContains('main', 'Drink added to your menu.');
        self::assertSelectorTextContains('article', 'Matcha Latte');
        self::assertSelectorTextContains('article', '6.50 EUR');

        // An existing menu entry must not be duplicated through the form.
        $this->client->submit($crawler->selectButton('Add to menu')->form([
            'store_menu_item[bubbleTea]' => (string) $drinkId,
            'store_menu_item[price]' => '7.00',
        ]));
        self::assertSelectorTextContains('form', 'This drink is already on this menu.');
        $manager = static::getContainer()->get(EntityManagerInterface::class);
        $item = $manager->getRepository(StoreBubbleTea::class)->findOneBy([]);
        self::assertSame(1, $manager->getRepository(StoreBubbleTea::class)->count([]));
        self::assertSame($ownId, $item->getStoreId()->getId());
        self::assertSame('EUR', $item->getStoreId()->getCurrency()->getCode());
        self::assertSame('6.50', number_format((float) $item->getPrice(), 2, '.', ''));
        self::assertTrue($item->isAvailable());
        self::assertNotNull($item->getCreatedAt());

        $this->client->request('PATCH', '/api/stores/'.$otherId, server: ['CONTENT_TYPE' => 'application/merge-patch+json'], content: '{"name":"Changed"}');
        self::assertResponseStatusCodeSame(403);
        $this->client->request('GET', '/my-locations/'.$otherId.'/menu');
        self::assertResponseStatusCodeSame(403);
    }

    public function testOwnerCanSetLocationCurrency(): void
    {
        $owner = $this->user('owner@example.com', ['ROLE_OWNER']);
        $other = $this->user('other@example.com', ['ROLE_OWNER']);
        $store = $this->store('Currency location', $owner);
        $otherStore = $this->store('Other location', $other);
        $currency = (new Currency())->setCode('EUR')->setName('Euro')->setSymbol('€')->setIsActive(true);
        $this->entityManager->persist($currency);
        $this->entityManager->flush();
        $storeId = $store->getId();
        $currencyId = $currency->getId();
        $otherId = $otherStore->getId();
        $this->client->loginUser($owner);

        $this->client->request('GET', '/my-locations/'.$storeId.'/menu');
        self::assertSelectorTextContains('main', 'Choose a currency for this location before adding drinks.');
        self::assertSelectorNotExists('button[type="submit"]');
        $this->client->request('POST', '/my-locations/'.$storeId.'/menu');
        self::assertSame(0, static::getContainer()->get(EntityManagerInterface::class)->getRepository(StoreBubbleTea::class)->count([]));

        $this->client->request('GET', '/my-locations/'.$otherId.'/edit');
        self::assertResponseStatusCodeSame(403);
        $this->client->request('POST', '/my-locations/'.$otherId.'/edit');
        self::assertResponseStatusCodeSame(403);

        $crawler = $this->client->request('GET', '/my-locations/'.$storeId.'/edit');
        self::assertResponseIsSuccessful();
        self::assertSelectorExists('select[name="store[currency]"]');
        $this->client->submit($crawler->selectButton('Save')->form([
            'store[currency]' => (string) $currencyId,
        ]));
        self::assertResponseRedirects('/my-locations/'.$storeId.'/menu');
        $this->client->followRedirect();
        self::assertSelectorTextContains('main', 'Currency: EUR');
        self::assertSelectorExists('button[type="submit"]');
        self::assertSelectorNotExists('[name="store_menu_item[currency]"]');
        $saved = static::getContainer()->get(EntityManagerInterface::class)->find(Store::class, $storeId);
        self::assertSame('EUR', $saved->getCurrency()->getCode());
    }

    public function testGuestMustLogIn(): void
    {
        $this->client->request('GET', '/my-locations');
        self::assertResponseRedirects('/login');
        $this->client->request('GET', '/store');
        self::assertResponseRedirects('/login');
    }

    private function user(string $email, array $roles): User
    {
        $user = (new User())->setEmail($email)->setUsername($email)->setName($email)
            ->setPassword('unused')->setRoles($roles)
            ->setCreatedAt(new \DateTimeImmutable())->setCguValidatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function store(string $name, User $owner): Store
    {
        $store = (new Store())->setName($name)->setAddress('10 Rue de Paris')->setCity('Paris')
            ->setCountry('France')->setPostalCode('75001')->setCreatedBy($owner)->setCreatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($store);
        $this->entityManager->flush();

        return $store;
    }
}
