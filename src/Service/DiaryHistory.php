<?php

namespace App\Service;

use App\Entity\DrinkLog;
use App\Repository\DrinkLogRepository;

final class DiaryHistory
{
    public function __construct(private readonly DrinkLogRepository $drinkLogs)
    {
    }

    /**
     * @return array<string, array{
     *     label: string,
     *     entries: list<array{0: DrinkLog, drinkName: ?string, drinkImage: ?string, storeName: ?string, storeCity: ?string}>,
     *     ratingTotal: float,
     * }>
     */
    public function forUser(int $userId): array
    {
        $months = [];
        $timezone = new \DateTimeZone('Europe/Paris');

        foreach ($this->drinkLogs->findEntriesForUser($userId) as $entry) {
            $log = $entry[0];
            $date = $log->getConsumedAt()->setTimezone($timezone);
            $month = $date->format('Y-m');

            $months[$month] ??= [
                'label' => $date->format('F Y'),
                'entries' => [],
                'ratingTotal' => 0.0,
            ];
            $months[$month]['entries'][] = $entry;
            $months[$month]['ratingTotal'] += (float) $log->getRating();
        }

        return $months;
    }
}
