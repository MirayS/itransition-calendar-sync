<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Calendar;
use Doctrine\ORM\EntityManagerInterface;

class GoogleCalendarParser implements CalendarParserInterface
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
        $googleCalendarService = new GoogleCalendarService($this->googleAuthService->getGoogleClient());
        $nextPageToken = "";
        do {
            $result = $googleCalendarService->getEvents($calendar->getCalendarId(), $nextPageToken, isset($calendar->getMetaData()["lastSyncToken"]) ? $calendar->getMetaData()["lastSyncToken"] : "");
            foreach ($result["events"] as $event) {
                $eventModel = $this->eventService->getOrCreateEvent($event["id"],
                    $calendar,
                    $event["name"] ?? "",
                    $event["start"] == null ? new \DateTime('now') : new \DateTime($event["start"]),
                    $event["end"] == null ? new \DateTime('now') : new \DateTime($event["end"]),
                    $event["isAllDay"] ?? false,
                    $event["description"]
                );
                if ($event["status"] == self::CANCEL_STATUS) {
                    $this->entityManager->remove($eventModel);
                    continue;
                }
                $this->entityManager->persist($eventModel);
            }
            $nextPageToken = $result["nextPageToken"];
        } while ((!isset($result["syncToken"])));
        $metaData = $calendar->getMetaData();
        $metaData["lastSyncToken"] = $result["syncToken"];
        if (isset($metaData["notificationExpirationDate"]) && new \DateTime($metaData["notificationExpirationDate"]) < new \DateTime('now')) {
            $this->googleNotificationService->startReceiveNotification($calendar);
        }
        $calendar->setMetaData($metaData);
        $calendar->setLastSyncDate(new \DateTime('now'));
        $this->entityManager->persist($calendar);
        $this->entityManager->flush();
    }

    private function updateTokens(Calendar $calendar)
    {
        $this->googleAuthService->setAccessToken($calendar->getAccessToken());
        $newTokens = $this->googleAuthService->getNewAccessToken($calendar->getRefreshToken());
        $calendar->setAccessToken($newTokens->getAccessToken());
        $calendar->setRefreshToken($newTokens->getRefreshToken());
    }
}