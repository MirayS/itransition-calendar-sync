<?php

declare(strict_types=1);

namespace App\Service\GoogleService;

use App\Entity\Calendar;
use App\Service\EventNotificationSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;

class GoogleNotificationService implements EventNotificationSubscriberInterface
{
    private const META_NOTIFICATION_RESOURCE = 'notificationResourceId';

    private UrlGeneratorInterface $urlGenerator;
    private GoogleClientService $googleClientService;

    public function __construct(GoogleClientService $googleClientService, UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
        $this->googleClientService = $googleClientService;
    }

    public function subscribe(string $refreshToken, string $calendarId): array
    {
        $this->googleClientService->loadAccessToken($refreshToken);
        $googleCalendarService = $this->googleClientService->getGoogleCalendarClient();
        $requestModel = $this->getRequestModel();
        $result = $googleCalendarService->events->watch($calendarId, $requestModel);

        return $this->getMetaData($result->getId(), date(\DateTimeImmutable::RFC3339_EXTENDED, $result->getExpiration() / 1000), $result->getResourceId());
    }

    public function cancelSubscription(string $refreshToken, string $calendarId, array $metaData): array
    {
        if (isset($metaData[self::META_NOTIFICATION_RESOURCE], $metaData[Calendar::SUBSCRIPTION_ID_META], $metaData[Calendar::SUBSCRIPTION_EXPIRATION_META])) {
            $this->googleClientService->loadAccessToken($refreshToken);
            $googleCalendarService = $this->googleClientService->getGoogleCalendarClient();
            $requestModel = $this->getRequestModel($metaData[Calendar::SUBSCRIPTION_ID_META], $metaData[self::META_NOTIFICATION_RESOURCE]);
            $googleCalendarService->channels->stop($requestModel);
        }

        return $this->getMetaData();
    }

    public function updateSubscription(string $refreshToken, string $calendarId, array $metaData): array
    {
        $this->cancelSubscription($refreshToken, $calendarId, $metaData);

        return $this->subscribe($refreshToken, $calendarId);
    }

    /**
     * @return null[]|string[]
     */
    private function getMetaData(?string $notificationId = null, ?string $expirationDate = null, ?string $resourceId = null): array
    {
        return [
            Calendar::SUBSCRIPTION_ID_META => $notificationId,
            Calendar::SUBSCRIPTION_EXPIRATION_META => $expirationDate,
            self::META_NOTIFICATION_RESOURCE => $resourceId,
        ];
    }

    private function getRequestModel(?string $subscriptionId = null, ?string $subscriptionResource = null): \Google_Service_Calendar_Channel
    {
        $params = new \Google_Service_Calendar_Channel();
        $params->setAddress($this->urlGenerator->generate('api.google.notification', [], UrlGeneratorInterface::ABSOLUTE_URL));
        $params->setType('web_hook');

        if (null != $subscriptionId && null != $subscriptionResource) {
            $params->setId($subscriptionId);
            $params->setResourceId($subscriptionResource);
        } else {
            $params->setId(Uuid::v4()->toRfc4122());
        }

        return $params;
    }
}
