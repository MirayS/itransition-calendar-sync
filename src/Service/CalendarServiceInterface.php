<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Calendar;

interface CalendarServiceInterface
{
    public function getCalendars(string $accessToken): array;

    public function parseEvents(Calendar $calendar): void;
}
