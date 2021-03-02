<?php

declare(strict_types=1);

namespace App\Service\GoogleService;

use App\Service\CalendarEventsParserInterface;
use App\Service\Model\EventsParserResult;
use App\Service\Model\ParsedEvent;

class GoogleCalendarEventsParser implements CalendarEventsParserInterface
{
    private const CANCEL_STATUS = 'cancelled';

    private GoogleClientService $googleClientService;

    public function __construct(GoogleClientService $googleClientService)
    {
        $this->googleClientService = $googleClientService;
    }

    public function parseEvents(string $refreshToken, string $calendarId, ?string $syncToken = null): EventsParserResult
    {
        $this->googleClientService->loadAccessToken($refreshToken);
        $nextPageToken = '';
        $requestResult = null;
        $parsedEvents = [];
        while (true) {
            $requestResult = $this->getEvents($calendarId, $nextPageToken, $syncToken ?? '');
            $parsedEvents = array_merge($parsedEvents, $this->parseEventsFromObject($requestResult->getItems()));

            if (null != $requestResult->getNextSyncToken()) {
                break;
            }

            $nextPageToken = $requestResult->getNextPageToken();
        }

        return new EventsParserResult($requestResult->getNextSyncToken(), $parsedEvents);
    }

    private function getEvents(string $calendarId, ?string $pageToken, ?string $lastSyncToken): \Google_Service_Calendar_Events
    {
        $params = [];
        if (null != $pageToken) {
            $params['pageToken'] = $pageToken;
        }
        if (null != $lastSyncToken) {
            $params['syncToken'] = $lastSyncToken;
        }

        $googleCalendarService = $this->googleClientService->getGoogleCalendarClient();

        return $googleCalendarService->events->listEvents($calendarId, $params);
    }

    /**
     * @param \Google_Service_Calendar_Event[] $eventsObject
     *
     * @return ParsedEvent[]
     */
    private function parseEventsFromObject(array $eventsObject): array
    {
        $events = [];
        foreach ($eventsObject as $eventObject) {
            $events[] = new ParsedEvent(
                $eventObject->getId(),
                self::CANCEL_STATUS == $eventObject->getStatus(),
                $eventObject->getSummary(),
                $eventObject->getDescription(),
                $this->getEventDateTime($eventObject->getStart()),
                $this->getEventDateTime($eventObject->getEnd()),
                $this->isAllDayEvent($eventObject)
            );
        }

        return $events;
    }

    private function getEventDateTime(?\Google_Service_Calendar_EventDateTime $eventDateTime): ?\DateTimeImmutable
    {
        if (null == $eventDateTime) {
            return null;
        }

        $timeZone = $eventDateTime->getTimeZone() ?? 'UTC';
        $timeZoneObject = new \DateTimeZone($timeZone);
        if (null == $eventDateTime->getDateTime()) {
            return new \DateTimeImmutable($eventDateTime->getDate(), $timeZoneObject);
        }

        return new \DateTimeImmutable($eventDateTime->getDateTime(), $timeZoneObject);
    }

    private function isAllDayEvent(\Google_Service_Calendar_Event $googleEvent): bool
    {
        if (null == $googleEvent->getStart()) {
            return false;
        }

        return null == $googleEvent->getStart()->getDateTime();
    }
}
