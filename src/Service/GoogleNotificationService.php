<?php
declare(strict_types=1);

namespace App\Service;


use App\Entity\Calendar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class GoogleNotificationService
{
    private GoogleAuthService $googleAuthService;
    private UrlGeneratorInterface $urlGenerator;
    private EntityManagerInterface $entityManager;

    public function __construct(GoogleAuthService $googleAuthService, UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager)
    {
        $this->googleAuthService = $googleAuthService;
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;
    }

    public function startReceiveNotification(Calendar $calendar)
    {
        $metaData = $calendar->getMetaData();
        if (isset($metaData["notificationId"], $metaData["notificationResourceId"], $metaData["notificationExpirationDate"]) && new \DateTime($metaData["notificationExpirationDate"]) >= new \DateTime('now')) {
            $this->stopReceiveNotification($calendar);
        }
        $params = $this->getRequestModel();
        $this->updateTokens($calendar);
        $googleCalendarService = $this->getGoogleCalendarService($this->googleAuthService->getGoogleClient());
        $result = $googleCalendarService->events->watch($calendar->getCalendarId(), $params);
        $this->updateCalendarModel($calendar, $result);
        $this->entityManager->persist($calendar);
        $this->entityManager->flush();
    }

    public function stopReceiveNotification(Calendar $calendar)
    {
        $metaData = $calendar->getMetaData();

        if (!isset($metaData["notificationId"], $metaData["notificationResourceId"], $metaData["notificationExpirationDate"]))
            return;

        $this->updateTokens($calendar);
        $googleCalendarService = $this->getGoogleCalendarService($this->googleAuthService->getGoogleClient());
        $params = new \Google_Service_Calendar_Channel();
        $params->setId($metaData["notificationId"]);
        $params->setResourceId($metaData["notificationResourceId"]);
        $googleCalendarService->channels->stop($params);
        $metaData["notificationId"] = null;
        $metaData["notificationExpirationDate"] = null;
        $metaData["notificationResourceId"] = null;
        $calendar->setMetaData($metaData);
        $this->entityManager->persist($calendar);
        $this->entityManager->flush();
    }

    private function updateCalendarModel(Calendar $calendar, \Google_Service_Calendar_Channel $calendarChannel)
    {
        $metaData = $calendar->getMetaData();
        $metaData["notificationExpirationDate"] = date("Y-m-d H:i:s", $calendarChannel->getExpiration() / 1000);
        $metaData["notificationId"] = $calendarChannel->getId();
        $metaData["notificationResourceId"] = $calendarChannel->getResourceId();
        $calendar->setMetaData($metaData);
    }

    private function getRequestModel(): \Google_Service_Calendar_Channel
    {
        $params = new \Google_Service_Calendar_Channel();
        $params->setAddress($this->urlGenerator->generate("api.google.notification", [], UrlGeneratorInterface::ABSOLUTE_URL));
        $params->setType("web_hook");
        $params->setId(Uuid::v4()->toRfc4122());
        return $params;
    }

    private function getGoogleCalendarService(\Google_Client $googleClient): \Google_Service_Calendar
    {
        return new \Google_Service_Calendar($googleClient);
    }

    private function updateTokens(Calendar $calendar)
    {
        $this->googleAuthService->setAccessToken($calendar->getAccessToken());
        $newTokens = $this->googleAuthService->getNewAccessToken($calendar->getRefreshToken());
        $calendar->setAccessToken($newTokens->getAccessToken());
        $calendar->setRefreshToken($newTokens->getRefreshToken());
    }
}