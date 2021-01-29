<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\CalendarRepository;

class GoogleCalendarParser implements CalendarParserInterface
{
    private \Google_Client $apiClient;
    private CalendarRepository $calendarRepository;

    public function __construct(\Google_Client $client, CalendarRepository $calendarRepository)
    {
        $this->apiClient = $client;
        $this->calendarRepository = $calendarRepository;
    }

    public function syncNow(int $calendarId)
    {

    }
}