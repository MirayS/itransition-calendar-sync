<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Calendar;
use Doctrine\ORM\EntityManagerInterface;

class GoogleCalendarParser implements CalendarParserInterface
{
    private GoogleAuthService $googleAuthService;
    private EntityManagerInterface $entityManager;
    private EventService $eventService;

    public function __construct(GoogleAuthService $googleAuthService, EntityManagerInterface $entityManager, EventService $eventService)
    {
        $this->googleAuthService = $googleAuthService;
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
    }

    public function parseEvents(Calendar $calendar)
    {
        $this->updateTokens($calendar);
        $googleCalendarService = new GoogleCalendarService($this->googleAuthService->getGoogleClient());
        $nextPageToken = "";
        do {
            $result = $googleCalendarService->getEvents($calendar->getCalendarId(), $nextPageToken, isset($calendar->getMetaData()["lastSyncToken"]) ? $calendar->getMetaData()["lastSyncToken"] : "");
            foreach ($result["events"] as $event) {
                $eventModel = $this->eventService->getOrCreateEvent($event["id"], $calendar, $event["name"] ?? "", new \DateTime($event["start"]), new \DateTime($event["end"]), $event["isAllDay"], $event["description"]);
                $this->entityManager->persist($eventModel);
            }
            $nextPageToken = $result["nextPageToken"];
        } while ((!isset($result["syncToken"])));
        $calendar->setMetaData(["lastSyncToken" => $result["syncToken"]]);
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