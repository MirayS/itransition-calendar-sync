<?php

declare(strict_types=1);

namespace App\Service\GoogleService;

use App\Service\GoogleService\Model\GoogleCalendar;

class GoogleCalendarParser
{
    private GoogleClientService $googleClientService;

    public function __construct(GoogleClientService $googleClientService)
    {
        $this->googleClientService = $googleClientService;
    }

    /**
     * @return GoogleCalendar[]
     */
    public function getCalendars(string $refreshToken): array
    {
        $result = [];
        $this->googleClientService->loadAccessToken($refreshToken);
        $googleCalendarService = $this->googleClientService->getGoogleCalendarClient();
        foreach ($googleCalendarService->calendarList->listCalendarList()->getItems() as $item) {
            $result[] = new GoogleCalendar($item->getId(), $item->getSummary());
        }

        return $result;
    }
}
