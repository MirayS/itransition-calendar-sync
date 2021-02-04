<?php
declare(strict_types=1);

namespace App\Service;


interface CalendarServiceInterface
{
    public function getCalendars(): array;

    public function getEvents(string $calendarId, ?string $pageToken, ?string $lastSyncToken): array;
}