<?php

declare(strict_types=1);

namespace App\Service\GoogleService;

use App\Entity\Calendar;
use App\Service\CalendarEntityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class GoogleNotificationService
{
    private const META_NOTIFICATION_ID = 'notificationId';
    private const META_NOTIFICATION_EXPIRATION = 'notificationExpirationDate';
    private const META_NOTIFICATION_RESOURCE = 'notificationResourceId';

    private UrlGeneratorInterface $urlGenerator;
    private EntityManagerInterface $entityManager;
    private GoogleClientService $googleClientService;

    private CalendarEntityService $calendarService;

    public function __construct(GoogleClientService $googleClientService, UrlGeneratorInterface $urlGenerator, EntityManagerInterface $entityManager, CalendarEntityService $calendarService)
    {
        $this->urlGenerator = $urlGenerator;
        $this->entityManager = $entityManager;
        $this->googleClientService = $googleClientService;
        $this->calendarService = $calendarService;
    }

    public function startReceiveNotification(Calendar $calendar): void
    {
        $metaData = $calendar->getMetaData();
        if (isset($metaData, $metaData[self::META_NOTIFICATION_EXPIRATION], $metaData[self::META_NOTIFICATION_RESOURCE], $metaData[self::META_NOTIFICATION_ID]) && new \DateTime($metaData[self::META_NOTIFICATION_EXPIRATION]) >= new \DateTime('now')) {
            $this->stopReceiveNotification($calendar);
        }

        $this->googleClientService->loadAccessToken($calendar->getRefreshToken());
        $googleCalendarService = $this->googleClientService->getGoogleCalendarClient();
        $requestModel = $this->getRequestModel();
        $result = $googleCalendarService->events->watch($calendar->getCalendarId(), $requestModel);
        $this->fillMetaData($calendar, $result->getId(), date('Y-m-d H:i:s', $result->getExpiration() / 1000), $result->getResourceId());
        $this->calendarService->updateCalendar($calendar);
    }

    public function stopReceiveNotification(Calendar $calendar): void
    {
        $metaData = $calendar->getMetaData();
        if (!isset($metaData, $metaData[self::META_NOTIFICATION_RESOURCE], $metaData[self::META_NOTIFICATION_ID], $metaData[self::META_NOTIFICATION_EXPIRATION])) {
            return;
        }

        $this->googleClientService->loadAccessToken($calendar->getRefreshToken());
        $googleCalendarService = $this->googleClientService->getGoogleCalendarClient();
        $requestModel = $this->getRequestModel($calendar);
        $googleCalendarService->channels->stop($requestModel);
        $this->fillMetaData($calendar);
        $this->calendarService->updateCalendar($calendar);
    }

    public function checkNotificationSubscribe(Calendar $calendar): void
    {
        $metaData = $calendar->getMetaData();
        if (isset($metaData[self::META_NOTIFICATION_EXPIRATION], $metaData[self::META_NOTIFICATION_ID], $metaData[self::META_NOTIFICATION_RESOURCE]) && new \DateTime($metaData[self::META_NOTIFICATION_EXPIRATION]) < new \DateTime('now')) {
            $this->startReceiveNotification($calendar);
        }
    }

    private function getRequestModel(?Calendar $calendar = null): \Google_Service_Calendar_Channel
    {
        $params = new \Google_Service_Calendar_Channel();
        $params->setAddress($this->urlGenerator->generate('api.google.notification', [], UrlGeneratorInterface::ABSOLUTE_URL));
        $params->setType('web_hook');

        $metaData = null == $calendar ? null : $calendar->getMetaData();

        if (null != $metaData && isset($metaData[self::META_NOTIFICATION_ID], $metaData[self::META_NOTIFICATION_RESOURCE])) {
            $params->setId($metaData[self::META_NOTIFICATION_ID]);
            $params->setResourceId($metaData[self::META_NOTIFICATION_RESOURCE]);
        } else {
            $params->setId(Uuid::v4()->toRfc4122());
        }

        return $params;
    }

    private function fillMetaData(Calendar $calendar, ?string $notificationId = null, ?string $expirationDate = null, ?string $resourceId = null): void
    {
        $calendar->fillMetaData([
            self::META_NOTIFICATION_ID => $notificationId,
            self::META_NOTIFICATION_EXPIRATION => $expirationDate,
            self::META_NOTIFICATION_RESOURCE => $resourceId,
        ]);
    }
}
