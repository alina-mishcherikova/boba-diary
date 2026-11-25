<?php

namespace App\Repository;

use App\Entity\DrinkLog;
use App\Entity\StoreBubbleTea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DrinkLog>
 */
class DrinkLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DrinkLog::class);
    }

    /** @return array{drinksTried: int, storesVisited: int} */
    public function getProfileStats(int $userId): array
    {
        $stats = $this->forUser($userId)
            ->select('COUNT(log.id) AS drinksTried', 'COUNT(DISTINCT IDENTITY(menu.store_id)) AS storesVisited')
            ->getQuery()
            ->getSingleResult();

        return [
            'drinksTried' => (int) $stats['drinksTried'],
            'storesVisited' => (int) $stats['storesVisited'],
        ];
    }

    /** @return list<array{0: DrinkLog, drinkName: ?string, drinkImage: ?string, storeName: ?string, storeCity: ?string}> */
    public function findEntriesForUser(int $userId, ?int $limit = null): array
    {
        return $this->forUser($userId)
            ->addSelect('tea.name AS drinkName', 'tea.image_url AS drinkImage', 'store.name AS storeName', 'store.city AS storeCity')
            ->leftJoin('menu.bubbleTea', 'tea')
            ->leftJoin('menu.store_id', 'store')
            ->orderBy('log.consumed_at', 'DESC')
            ->addOrderBy('log.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    private function forUser(int $userId): QueryBuilder
    {
        return $this->createQueryBuilder('log')
            ->leftJoin(StoreBubbleTea::class, 'menu', 'ON', 'menu.id = log.store_bubble_tea_id')
            ->andWhere('log.user_id = :userId')
            ->setParameter('userId', $userId);
    }
}
