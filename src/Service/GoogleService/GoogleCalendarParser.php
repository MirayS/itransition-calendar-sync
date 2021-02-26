<?php

declare(strict_types=1);

namespace App\Service\GoogleService;

use App\Entity\Calendar;
use App\Service\CalendarParserInterface;
use App\Service\GoogleService\Model\GoogleCalendar;

class GoogleCalendarParser implements CalendarParserInterface
{
    private GoogleClientService $googleClientService;

    public function __construct(GoogleClientService $googleClientService)
    {
        $this->googleClientService = $googleClientService;
    }

    /**
     * @return Calendar[]
     */
    public function parseCalendars(string $refreshToken): array
    {
        $result = [];
        $this->googleClientService->loadAccessToken($refreshToken);
        $googleCalendarService = $this->googleClientService->getGoogleCalendarClient();
        foreach ($googleCalendarService->calendarList->listCalendarList()->getItems() as $item) {
            $result[] = new Calendar($item->getId(), $item->getSummary());
        }

        return $result;
    }
}
