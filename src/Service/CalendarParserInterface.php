<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Calendar;

interface CalendarParserInterface
{
    public function parseEvents(Calendar $calendar);
}