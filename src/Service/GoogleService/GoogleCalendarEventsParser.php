<?php

declare(strict_types=1);

namespace App\Service\GoogleService;

use App\Entity\Calendar;
use App\Service\CalendarEntityService;
use App\Service\EventEntityService;

class GoogleCalendarEventsParser
{
    private const CANCEL_STATUS = 'cancelled';
    private const META_LAST_SYNC_TOKEN = 'lastSyncToken';

    private GoogleClientService $googleClientService;
    private CalendarEntityService $calendarService;
    private GoogleNotificationService $googleNotificationService;
    private EventEntityService $eventService;

    public function __construct(GoogleClientService $googleClientService, CalendarEntityService $calendarService, GoogleNotificationService $googleNotificationService, EventEntityService $eventService)
    {
        $this->googleClientService = $googleClientService;
        $this->calendarService = $calendarService;
        $this->googleNotificationService = $googleNotificationService;
        $this->eventService = $eventService;
    }

    public function parseEvents(Calendar $calendar): void
    {
        $this->googleClientService->loadAccessToken($calendar->getRefreshToken());
        $nextPageToken = '';
        $result = null;
        while (null == $result || null == $result->getNextSyncToken()) {
            $result = $this->getEvents($calendar->getCalendarId(), $nextPageToken, isset($calendar->getMetaData()[self::META_LAST_SYNC_TOKEN]) ? $calendar->getMetaData()[self::META_LAST_SYNC_TOKEN] : '');
            foreach ($result->getItems() as $event) {
                $this->parseEvent($calendar, $event);
            }
            $nextPageToken = $result->getNextPageToken();
        }
        $nextSyncToken = null == $result ? null : $result->getNextSyncToken();
        $calendar->fillMetaData([
            self::META_LAST_SYNC_TOKEN => $nextSyncToken,
        ]);

        $this->eventService->saveChanges();
        $this->googleNotificationService->checkNotificationSubscribe($calendar);
        $calendar->setLastSyncDate(new \DateTime('now'));
        $this->calendarService->updateCalendar($calendar);
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

    private function parseEvent(Calendar $calendar, \Google_Service_Calendar_Event $googleEvent): void
    {
        $eventModel = $this->eventService->getOrCreateEvent($googleEvent->getId(),
            $calendar,
            $googleEvent->getSummary() ?? '',
            $this->getEventDateTime($googleEvent->getStart()),
            $this->getEventDateTime($googleEvent->getEnd()),
            $this->isAllDayEvent($googleEvent),
            $googleEvent->getDescription() ?? ''
        );
        if (self::CANCEL_STATUS == $googleEvent->getStatus()) {
            $this->eventService->removeEvent($eventModel, false);
        } else {
            $this->eventService->updateEvent($eventModel, false);
        }
    }

    private function getEventDateTime(?\Google_Service_Calendar_EventDateTime $eventDateTime): \DateTime
    {
        if (null == $eventDateTime) {
            return new \DateTime('now');
        }
        if (null == $eventDateTime->getDateTime()) {
            return new \DateTime($eventDateTime->getDate());
        }

        return new \DateTime($eventDateTime->getDateTime());
    }

    private function isAllDayEvent(\Google_Service_Calendar_Event $googleEvent): bool
    {
        if (null == $googleEvent->getStart()) {
            return false;
        }

        return null == $googleEvent->getStart()->getDateTime();
    }
}
