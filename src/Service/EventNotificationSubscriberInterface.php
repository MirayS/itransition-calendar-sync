<?php

declare(strict_types=1);

namespace App\Service;

interface EventNotificationSubscriberInterface
{
    /**
     * @return string[] | null[]
     */
    public function subscribe(string $refreshToken, string $calendarId): array;

    /**
     * @param string[] | null[] $metaData
     *
     * @return string[] | null[]
     */
    public function cancelSubscription(string $refreshToken, string $calendarId, array $metaData): array;

    /**
     * @param string[] | null[] $metaData
     *
     * @return string[] | null[]
     */
    public function updateSubscription(string $refreshToken, string $calendarId, array $metaData): array;
}
