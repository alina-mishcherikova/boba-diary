<?php

namespace App\Tests\Controller;

use App\Entity\BubbleTea;
use App\Entity\DrinkLog;
use App\Entity\Store;
use App\Entity\StoreBubbleTea;
use App\Entity\User;
use App\Enum\IceLevel;
use App\Enum\SugarLevel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProfileControllerTest extends WebTestCase
{
    private string $databasePath;
    private ?string $previousEnv;
    private ?string $previousServer;
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->databasePath = tempnam(sys_get_temp_dir(), 'profile-test-');
        $this->previousEnv = $_ENV['DATABASE_URL'] ?? null;
        $this->previousServer = $_SERVER['DATABASE_URL'] ?? null;
        $_ENV['DATABASE_URL'] = $_SERVER['DATABASE_URL'] = 'sqlite:///'.$this->databasePath;

        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        (new SchemaTool($this->entityManager))->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach (['_ENV' => $this->previousEnv, '_SERVER' => $this->previousServer] as $global => $previousValue) {
            if (null === $previousValue) {
                unset($GLOBALS[$global]['DATABASE_URL']);
            } else {
                $GLOBALS[$global]['DATABASE_URL'] = $previousValue;
            }
        }

        unlink($this->databasePath);
    }

    public function testProfileRequiresSignInAndShowsEmptyStates(): void
    {
        $user = $this->createUser('mira');
        $this->entityManager->flush();

        $this->client->request('GET', '/profile');
        self::assertResponseRedirects('/login');

        $this->client->loginUser($user);
        $this->client->request('GET', '/profile');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Mira');
        self::assertSelectorTextContains('.profile-identity', '@mira');
        self::assertSelectorTextContains('.profile-recent .profile-empty', 'logged any drinks yet');
        self::assertSelectorTextContains('.profile-added .profile-empty', 'added any drinks');
        self::assertSelectorCount(2, '.profile-topbar-actions button[disabled]');
        self::assertSelectorCount(2, '.profile-summary-actions button[disabled]');
        self::assertSelectorCount(2, '.profile-topbar-actions button > svg[aria-hidden="true"]');
        self::assertSelectorCount(2, '.profile-summary-actions button > svg[aria-hidden="true"]');
        self::assertSelectorNotExists('.profile-nav a[href="/my-locations"]');
        self::assertSelectorExists('.profile-suggest a[href="/bubble/tea"]');
        self::assertSelectorNotExists('.profile-suggest a[href="/store"]');
    }

    public function testProfileDisplaysOnlyTheSignedInUsersActivity(): void
    {
        $now = new \DateTimeImmutable();
        $user = $this->createUser('mira')->setRoles(['ROLE_OWNER']);
        $other = $this->createUser('other');
        $tea = (new BubbleTea())
            ->setName('Matcha Pearl Latte')
            ->setImageUrl('/images/home/matcha-pearl-latte.png')
            ->setCreatedAt($now)
            ->setCreatedBy($user);
        $otherTea = (new BubbleTea())
            ->setName('Another user’s drink')
            ->setCreatedAt($now)
            ->setCreatedBy($other);
        $store = (new Store())
            ->setName('Tea Lab')
            ->setAddress('42 Main Street')
            ->setCity('Brooklyn')
            ->setCountry('US')
            ->setPostalCode('11201')
            ->setCreatedAt($now)
            ->setCreatedBy($user);
        $menuItem = (new StoreBubbleTea())
            ->setStoreId($store)
            ->setBubbleTea($tea)
            ->setPrice('5.00')
            ->setIsAvailable(true)
            ->setCreatedAt($now);

        foreach ([$tea, $otherTea, $store, $menuItem] as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();

        $this->addLog($user, $menuItem, $now->modify('-1 day'), '4.5', 'First tasting')
            ->setSugarLevel(SugarLevel::Half)
            ->setIceLevel(IceLevel::Less);
        $this->addLog($user, $menuItem, $now->modify('-2 days'), '4.0', 'Second tasting');
        $this->addLog($user, $menuItem, $now->modify('-8 days'), '5.0', 'Earlier tasting');
        $this->addLog($other, $menuItem, $now->modify('-1 day'), '1.0', 'Another user’s note');
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/profile');

        self::assertResponseIsSuccessful();
        self::assertSame(['3', '1'], $crawler->filter('.profile-stats dd')->each(static fn ($value) => $value->text()));
        self::assertSelectorCount(3, '.profile-entry');
        self::assertSelectorTextContains('.profile-entry:first-child', 'Matcha Pearl Latte');
        self::assertSelectorTextContains('.profile-entry:first-child', 'Tea Lab · Brooklyn');
        self::assertSelectorTextContains('.profile-entry:first-child', 'First tasting');
        self::assertSelectorTextContains('.profile-entry:first-child', '50% sugar');
        self::assertSelectorTextContains('.profile-entry:first-child', 'Less ice');
        self::assertSelectorExists('.profile-entry:first-child img[src$="/rating-half.svg"]');
        self::assertSelectorTextContains('.profile-entry:last-child', 'Earlier tasting');
        self::assertSelectorTextNotContains('main', 'Another user’s');
        self::assertSelectorCount(1, '.profile-added-row');
        self::assertSelectorTextContains('.profile-added-row', 'Matcha Pearl Latte');
        self::assertSelectorExists('.profile-suggest a[href="/store"]');
        self::assertSelectorExists('.profile-nav a[href="/my-locations"]');
    }

    public function testSignedInPagesShareNavigation(): void
    {
        $owner = $this->createUser('owner')->setRoles(['ROLE_OWNER']);
        $this->entityManager->flush();
        $this->client->loginUser($owner);

        foreach ([
            '/' => ['Home', 'page'],
            '/profile' => ['Profile', 'page'],
            '/my-locations' => ['My locations', 'page'],
            '/store' => ['My locations', 'location'],
            '/bubble/tea' => [null, null],
        ] as $url => [$currentLabel, $currentType]) {
            $crawler = $this->client->request('GET', $url);

            self::assertResponseIsSuccessful();
            self::assertSelectorCount(1, 'header.profile-topbar');
            self::assertSelectorNotExists('header a[href="/login"]');
            self::assertSame(['Home', 'Explore', 'My Diary', 'Profile', 'My locations'], $crawler->filter('.profile-nav > *')->each(static fn ($item) => $item->text()));
            self::assertSame(['Search', 'Log a drink'], $crawler->filter('.profile-topbar-actions button[disabled]')->each(static fn ($button) => $button->text()));
            self::assertSelectorCount(2, '.profile-nav [aria-disabled="true"]');

            if (null === $currentLabel) {
                self::assertSelectorNotExists('.profile-nav [aria-current]');
            } else {
                self::assertSelectorCount(1, '.profile-nav [aria-current]');
                self::assertSelectorTextContains('.profile-nav [aria-current="'.$currentType.'"]', $currentLabel);
            }
        }
    }

    private function createUser(string $username): User
    {
        $now = new \DateTimeImmutable();
        $user = (new User())
            ->setEmail($username.'@example.com')
            ->setUsername($username)
            ->setName(ucfirst($username))
            ->setPassword('unused-in-this-test')
            ->setCreatedAt($now)
            ->setCguValidatedAt($now);
        $this->entityManager->persist($user);

        return $user;
    }

    private function addLog(User $user, StoreBubbleTea $menuItem, \DateTimeImmutable $date, string $rating, string $note): DrinkLog
    {
        $log = (new DrinkLog())
            ->setUserId($user->getId())
            ->setStoreBubbleTeaId($menuItem->getId())
            ->setConsumedAt($date)
            ->setCreatedAt($date)
            ->setRating($rating)
            ->setNote($note);
        $this->entityManager->persist($log);

        return $log;
    }
}
