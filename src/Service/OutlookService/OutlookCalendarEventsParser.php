<?php

declare(strict_types=1);

namespace App\Service\OutlookService;

use App\Service\CalendarEventsParserInterface;
use App\Service\Model\EventsParserResult;
use App\Service\Model\ParsedEvent;
use Microsoft\Graph\Http\GraphResponse;
use Microsoft\Graph\Model\DateTimeTimeZone;
use Microsoft\Graph\Model\Event;

class OutlookCalendarEventsParser implements CalendarEventsParserInterface
{
    private OutlookClientService $clientService;

    public function __construct(OutlookClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function parseEvents(string $refreshToken, string $calendarId, ?string $lastSyncToken = null): EventsParserResult
    {
        $this->clientService->loadAccessToken($refreshToken);
        $url = $lastSyncToken ?? $this->getInitialUrl($calendarId);

        $events = [];
        $resultObject = null;
        while (true) {
            $resultObject = $this->getPagedObject($url);
            $events = array_merge($events, $this->parseEventsObject($resultObject->getResponseAsObject(Event::class)));

            if (null != $resultObject->getDeltaLink()) {
                break;
            }

            $url = $this->getEndPointFromUrl($resultObject->getNextLink());
        }

        return new EventsParserResult($this->getEndPointFromUrl($resultObject->getDeltaLink()), $events);
    }

    private function getPagedObject(string $url): GraphResponse
    {
        $result = $this->clientService->getApiClient()->createRequest('GET', $url)->execute();

        return $result;
    }

    private function getInitialUrl(string $calendarId): string
    {
        $params = [
            'startDateTime' => '1999-01-01T00:00:00',
            'endDateTime' => '2099-01-01T00:00:00',
        ];

        return '/me/calendars/'.$calendarId.'/calendarView/delta?'.http_build_query($params);
    }

    /**
     * @param Event[] $eventsObject
     *
     * @return ParsedEvent[]
     */
    private function parseEventsObject(array $eventsObject): array
    {
        $parsedEvents = [];

        foreach ($eventsObject as $eventObject) {
            $parsedEvents[] = new ParsedEvent(
                $eventObject->getId(),
                $eventObject->getIsCancelled(),
                $eventObject->getSubject(),
                $eventObject->getBodyPreview(),
                $this->parseDate($eventObject->getStart()),
                $this->parseDate($eventObject->getEnd()),
                $eventObject->getIsAllDay(),
            );
        }

        return $parsedEvents;
    }

    private function parseDate(?DateTimeTimeZone $date): ?\DateTimeImmutable
    {
        if (null == $date) {
            return null;
        }

        $timeZone = $date->getTimeZone() ?? 'UTC';

        return new \DateTimeImmutable($date->getDateTime(), new \DateTimeZone($timeZone));
    }

    private function getEndPointFromUrl(string $url): string
    {
        $startPosition = strpos($url, '/me');

        if (false === $startPosition) {
            return $url;
        }

        return substr($url, $startPosition);
    }
}
