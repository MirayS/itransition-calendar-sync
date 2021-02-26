<?php

declare(strict_types=1);

namespace App\Service;

use App\Service\Model\EventsParserResult;

interface CalendarEventsParser
{
    public function parseEvents(string $refreshToken, string $calendarId, ?string $lastSyncToken = null): EventsParserResult;
}
