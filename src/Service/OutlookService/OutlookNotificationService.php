<?php

declare(strict_types=1);

namespace App\Service\OutlookService;

use App\Entity\Calendar;
use App\Service\EventNotificationSubscriberInterface;
use Beta\Microsoft\Graph\Model\Notification;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OutlookNotificationService implements EventNotificationSubscriberInterface
{
    private OutlookClientService $clientService;

    private UrlGeneratorInterface $urlGenerator;

    public function __construct(OutlookClientService $clientService, UrlGeneratorInterface $urlGenerator)
    {
        $this->clientService = $clientService;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(string $refreshToken, string $calendarId): array
    {
        $this->clientService->loadAccessToken($refreshToken);

        $requestResult = $this->clientService->getApiClient()
            ->createRequest('POST', '/subscriptions')
            ->attachBody([
                'changeType' => 'updated',
                'notificationUrl' => $this->urlGenerator->generate('api.outlook.notification', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'resource' => '/me/calendars/'.$calendarId.'/events',
                'expirationDateTime' => (new \DateTimeImmutable('now +3 days'))->format(\DateTimeImmutable::RFC3339_EXTENDED),
            ])
            ->execute();

        $resultObject = $requestResult->getResponseAsObject(Notification::class);

        if (!$resultObject instanceof Notification) {
            return [];
        }

        return [
            Calendar::SUBSCRIPTION_ID_META => $resultObject->getId(),
            Calendar::SUBSCRIPTION_EXPIRATION_META => $resultObject->getExpirationDateTime()->format(\DateTime::RFC3339_EXTENDED),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function cancelSubscription(string $refreshToken, string $calendarId, array $metaData): array
    {
        $this->clientService->loadAccessToken($refreshToken);

        if (isset($metaData[Calendar::SUBSCRIPTION_ID_META], $metaData[Calendar::SUBSCRIPTION_EXPIRATION_META]) && new \DateTimeImmutable($metaData[Calendar::SUBSCRIPTION_EXPIRATION_META]) > new \DateTimeImmutable()) {
            $this->clientService->getApiClient()
                ->createRequest('DELETE', '/subscriptions/'.$metaData[Calendar::SUBSCRIPTION_ID_META])
                ->execute();
        }

        return [
            Calendar::SUBSCRIPTION_ID_META => null,
            Calendar::SUBSCRIPTION_EXPIRATION_META => null,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function updateSubscription(string $refreshToken, string $calendarId, array $metaData): array
    {
        $this->clientService->loadAccessToken($refreshToken);

        if (isset($metaData[Calendar::SUBSCRIPTION_ID_META], $metaData[Calendar::SUBSCRIPTION_EXPIRATION_META]) && new \DateTimeImmutable($metaData[Calendar::SUBSCRIPTION_EXPIRATION_META]) > new \DateTimeImmutable()) {
            $requestResult = $this->clientService->getApiClient()
                ->createRequest('PATCH', '/subscriptions/'.$metaData[Calendar::SUBSCRIPTION_ID_META])
                ->attachBody([
                    'expirationDateTime' => (new \DateTimeImmutable('now +3 days'))->format(\DateTimeImmutable::RFC3339_EXTENDED),
                ])
                ->execute();

            $resultObject = $requestResult->getResponseAsObject(Notification::class);

            if (!$resultObject instanceof Notification) {
                return [];
            }

            return [
                Calendar::SUBSCRIPTION_ID_META => $resultObject->getId(),
                Calendar::SUBSCRIPTION_EXPIRATION_META => $resultObject->getExpirationDateTime()->format(\DateTime::RFC3339_EXTENDED),
            ];
        }

        return [
            Calendar::SUBSCRIPTION_ID_META => null,
            Calendar::SUBSCRIPTION_EXPIRATION_META => null,
        ];
    }
}
