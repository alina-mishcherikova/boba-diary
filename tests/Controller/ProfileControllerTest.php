<?php

namespace App\Tests\Controller;

use App\Entity\BubbleTea;
use App\Entity\DrinkLog;
use App\Entity\Store;
use App\Entity\StoreBubbleTea;
use App\Entity\User;
use App\Enum\IceLevel;
use App\Enum\SugarLevel;
use App\Enum\Temperature;
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

        $this->client->request('GET', '/diary');
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
        self::assertSelectorExists('.profile-diary-link[href="/diary"]');

        $this->client->request('GET', '/diary');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.tasting-empty', 'logged any drinks yet');
    }

    public function testProfileAndDiaryDisplayOnlyTheSignedInUsersActivity(): void
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

        $this->addLog($user, $menuItem, new \DateTimeImmutable('2026-09-16 12:00'), '4.5', 'First tasting')
            ->setSugarLevel(SugarLevel::Half)
            ->setIceLevel(IceLevel::Less)
            ->setTemperature(Temperature::Cold)
            ->setPhotoUrl('/images/home/matcha-pearl-latte.png');
        $this->addLog($user, $menuItem, new \DateTimeImmutable('2026-09-11 12:00'), '4.0', 'Second tasting');
        $this->addLog($user, $menuItem, new \DateTimeImmutable('2026-08-28 12:00'), '5.0', 'Earlier tasting');
        $this->addLog($user, $menuItem, new \DateTimeImmutable('2026-08-19 12:00'), '5.0', 'Fourth tasting');
        $this->addLog($user, $menuItem, new \DateTimeImmutable('2026-08-05 12:00'), '5.0', 'Oldest tasting');
        $this->addLog($other, $menuItem, new \DateTimeImmutable('2026-10-11 12:00'), '1.0', 'Another user’s note');
        $this->entityManager->flush();
        $this->entityManager->clear();

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/profile');

        self::assertResponseIsSuccessful();
        self::assertSame(['5', '1'], $crawler->filter('.profile-stats dd')->each(static fn ($value) => $value->text()));
        self::assertSelectorCount(4, '.profile-entry');
        self::assertSelectorTextContains('.profile-entry:first-child', 'Matcha Pearl Latte');
        self::assertSelectorTextContains('.profile-entry:first-child', 'Tea Lab · Brooklyn');
        self::assertSelectorTextContains('.profile-entry:first-child', 'First tasting');
        self::assertSelectorTextContains('.profile-entry:first-child', '50% sugar');
        self::assertSelectorTextContains('.profile-entry:first-child', 'Less ice');
        self::assertSelectorExists('.profile-entry:first-child img[src$="/rating-half.svg"]');
        self::assertSelectorTextContains('.profile-entry:last-child', 'Fourth tasting');
        self::assertSelectorTextNotContains('main', 'Oldest tasting');
        self::assertSelectorTextNotContains('main', 'Another user’s');
        self::assertSelectorCount(1, '.profile-added-row');
        self::assertSelectorTextContains('.profile-added-row', 'Matcha Pearl Latte');
        self::assertSelectorExists('.profile-suggest a[href="/store"]');
        self::assertSelectorExists('.profile-nav a[href="/my-locations"]');

        $crawler = $this->client->request('GET', '/diary?user='.$other->getId());
        self::assertResponseIsSuccessful();
        self::assertSame(['September 2026', 'August 2026'], $crawler->filter('.tasting-month h2')->each(static fn ($heading) => $heading->text()));
        self::assertSelectorTextContains('.tasting-month:first-child .tasting-month-heading', '2 entries · avg 4.3');
        self::assertSelectorTextContains('.tasting-month:last-child .tasting-month-heading', '3 entries · avg 5.0');
        self::assertSelectorCount(5, '.tasting-entry');
        self::assertSelectorTextContains('.tasting-entry:first-child', 'Tea Lab · Brooklyn');
        self::assertSelectorTextContains('.tasting-entry:first-child', '50% sugar · cold · less ice');
        self::assertSelectorTextContains('.tasting-entry:first-child time', '16 Sep 2026');
        self::assertSelectorExists('.tasting-entry:first-child img.tasting-thumbnail[src="/images/home/matcha-pearl-latte.png"]');
        self::assertSelectorCount(4, '.tasting-thumbnail-empty');
        self::assertSelectorTextContains('.tasting-thumbnail-empty', 'No photo');
        self::assertSelectorNotExists('#month-2026-10');
        self::assertSelectorTextNotContains('main', 'Show older entries');
    }

    public function testSignedInPagesShareNavigation(): void
    {
        $owner = $this->createUser('owner')->setRoles(['ROLE_OWNER']);
        $this->entityManager->flush();
        $this->client->loginUser($owner);

        foreach ([
            '/' => ['Home', 'page'],
            '/profile' => ['Profile', 'page'],
            '/diary' => ['My Diary', 'page'],
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
            self::assertSelectorCount(1, '.profile-nav [aria-disabled="true"]');
            self::assertSelectorExists('.profile-nav a[href="/diary"]');

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
