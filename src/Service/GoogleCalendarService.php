<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Calendar;
use Doctrine\ORM\EntityManagerInterface;

class GoogleCalendarService implements CalendarServiceInterface
{
    private const CANCEL_STATUS = "cancelled";

    private GoogleAuthService $googleAuthService;
    private EntityManagerInterface $entityManager;
    private EventService $eventService;
    private GoogleNotificationService $googleNotificationService;

    public function __construct(GoogleAuthService $googleAuthService, EntityManagerInterface $entityManager, EventService $eventService, GoogleNotificationService $googleNotificationService)
    {
        $this->googleAuthService = $googleAuthService;
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->googleNotificationService = $googleNotificationService;
    }

    public function parseEvents(Calendar $calendar)
    {
        $this->updateTokens($calendar);
        $nextPageToken = "";
        $result = null;
        do {
            $result = $this->getEvents($calendar->getCalendarId(), $nextPageToken, isset($calendar->getMetaData()["lastSyncToken"]) ? $calendar->getMetaData()["lastSyncToken"] : "");
            foreach ($result->getItems() as $event) {
                $this->parseEvent($calendar, $event);
            }
            $nextPageToken = $result->getNextPageToken();
        } while ($result->getNextSyncToken() == null);
        $this->fillMetaData($calendar, [
            'lastSyncToken' => $result->getNextSyncToken(),
        ]);
        $this->checkNotificationSubscribe($calendar);
        $calendar->setLastSyncDate(new \DateTime('now'));
        $this->entityManager->persist($calendar);
        $this->entityManager->flush();
    }

    public function getCalendars(string $accessToken): array
    {
        $result = [];
        $this->googleAuthService->setAccessToken($accessToken);
        $googleCalendarService = new \Google_Service_Calendar($this->googleAuthService->getGoogleClient());
        foreach ($googleCalendarService->calendarList->listCalendarList()->getItems() as $item) {
            $result[] = [
                "id" => $item->getId(),
                "name" => $item->getSummary(),
            ];
        }

        return $result;
    }

    private function getEvents(string $calendarId, ?string $pageToken, ?string $lastSyncToken): \Google_Service_Calendar_Events
    {
        $params = [];
        if ($pageToken != null)
            $params["pageToken"] = $pageToken;
        if ($lastSyncToken != null)
            $params["syncToken"] = $lastSyncToken;

        $googleCalendarService = new \Google_Service_Calendar($this->googleAuthService->getGoogleClient());
        return $googleCalendarService->events->listEvents($calendarId, $params);
    }

    private function parseEvent(Calendar $calendar, \Google_Service_Calendar_Event $googleEvent)
    {
        $eventModel = $this->eventService->getOrCreateEvent($googleEvent->getId(),
            $calendar,
            $googleEvent->getSummary() ?? "",
            $this->getEventDateTime($googleEvent->getStart()),
            $this->getEventDateTime($googleEvent->getEnd()),
            $this->isAllDayEvent($googleEvent),
            $googleEvent->getDescription() ?? ""
        );
        if ($googleEvent->getStatus() == self::CANCEL_STATUS) {
            $this->entityManager->remove($eventModel);
        } else {
            $this->entityManager->persist($eventModel);
        }
    }

    private function getEventDateTime(?\Google_Service_Calendar_EventDateTime $eventDateTime): \DateTime
    {
        if ($eventDateTime == null) {
            return new \DateTime('now');
        }
        if ($eventDateTime->getDateTime() == null) {
            return new \DateTime($eventDateTime->getDate());
        }
        return new \DateTime($eventDateTime->getDateTime());
    }

    private function isAllDayEvent(\Google_Service_Calendar_Event $googleEvent): bool
    {
        if ($googleEvent->getStart() == null)
            return false;

        return $googleEvent->getStart()->getDateTime() == null;
    }

    private function updateTokens(Calendar $calendar)
    {
        $this->googleAuthService->setAccessToken($calendar->getAccessToken());
        $newTokens = $this->googleAuthService->getNewAccessToken($calendar->getRefreshToken());
        $calendar->setAccessToken($newTokens->getAccessToken());
        $calendar->setRefreshToken($newTokens->getRefreshToken());
    }

    private function fillMetaData(Calendar $calendar, array $newMetaData)
    {
        $metaData = $calendar->getMetaData();
        foreach ($newMetaData as $key => $value) {
            $metaData[$key] = $value;
        }
        $calendar->setMetaData($metaData);
    }

    private function checkNotificationSubscribe(Calendar $calendar) {
        $metaData = $calendar->getMetaData();
        if (isset($metaData["notificationExpirationDate"]) && new \DateTime($metaData["notificationExpirationDate"]) < new \DateTime('now')) {
            $this->googleNotificationService->startReceiveNotification($calendar);
        }
    }
}