<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Calendar;

interface CalendarParserInterface
{
    /**
     * @return Calendar[]
     */
    public function parseCalendars(string $refreshToken): array;
}
