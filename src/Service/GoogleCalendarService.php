<?php
declare(strict_types=1);

namespace App\Service;

class GoogleCalendarService implements CalendarServiceInterface
{
    private \Google_Service_Calendar $googleCalendarService;

    public function __construct(\Google_Client $googleClient)
    {
        $this->googleCalendarService = new \Google_Service_Calendar($googleClient);
    }

    public function getCalendars(): array
    {
        $result = [];
        foreach ($this->googleCalendarService->calendarList->listCalendarList()->getItems() as $item) {
            $result[] = [
                "id" => $item->getId(),
                "name" => $item->getSummary(),
            ];
        }

        return $result;
    }

    public function getEvents(string $calendarId, ?string $pageToken, ?string $lastSyncToken): array
    {
        $params = [];
        if ($pageToken != null)
            $params["pageToken"] = $pageToken;
        if ($lastSyncToken != null)
            $params["syncToken"] = $lastSyncToken;

        $events = [];

        $result = $this->googleCalendarService->events->listEvents($calendarId, $params);
        foreach ($result->getItems() as $item) {
            if ($item->getStart() == null || $item->getEnd() == null)
                continue;

            $events[] = [
                "name" => $item->getSummary(),
                "id" => $item->getId(),
                "start" => $item->getStart()->getDateTime() ?? $item->getStart()->getDate(),
                "end" => $item->getEnd()->getDateTime() ?? $item->getEnd()->getDate(),
                "isAllDay" => $item->getStart()->getDate() != null,
                "description" => $item->getDescription() ?? ""
            ];
        }
        return [
            "events" => $events,
            "nextPageToken" => $result->getNextPageToken(),
            "syncToken" => $result->getNextSyncToken()
        ];
    }
}