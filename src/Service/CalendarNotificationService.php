<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Calendar;
use App\Service\GoogleService\GoogleNotificationService;
use App\Service\OutlookService\OutlookNotificationService;
use Doctrine\ORM\EntityManagerInterface;

class CalendarNotificationService
{
    private OutlookNotificationService $outlookNotificationService;

    private EntityManagerInterface $entityManager;

    private GoogleNotificationService $googleNotificationService;

    public function __construct(OutlookNotificationService $outlookNotificationService, EntityManagerInterface $entityManager, GoogleNotificationService $googleNotificationService)
    {
        $this->outlookNotificationService = $outlookNotificationService;
        $this->entityManager = $entityManager;
        $this->googleNotificationService = $googleNotificationService;
    }

    public function subscribe(Calendar $calendar): void
    {
        $result = [];
        if ('outlook' == $calendar->getCalendarType()) {
            $result = $this->outlookNotificationService->subscribe($calendar->getRefreshToken(), $calendar->getCalendarId());
        } else {
            $result = $this->googleNotificationService->subscribe($calendar->getRefreshToken(), $calendar->getCalendarId());
        }
        $this->updateCalendarMetaData($calendar, $result);
    }

    public function updateSubscription(Calendar $calendar): void
    {
        $result = [];
        if ('outlook' == $calendar->getCalendarType()) {
            $result = $this->outlookNotificationService->updateSubscription($calendar->getRefreshToken(), $calendar->getCalendarId(), $calendar->getMetaData() ?? []);
        } else {
            $result = $this->googleNotificationService->updateSubscription($calendar->getRefreshToken(), $calendar->getCalendarId(), $calendar->getMetaData() ?? []);
        }
        $this->updateCalendarMetaData($calendar, $result);
    }

    public function cancelSubscription(Calendar $calendar): void
    {
        $result = [];
        if ('outlook' == $calendar->getCalendarType()) {
            $result = $this->outlookNotificationService->cancelSubscription($calendar->getRefreshToken(), $calendar->getCalendarId(), $calendar->getMetaData() ?? []);
        } else {
            $result = $this->googleNotificationService->cancelSubscription($calendar->getRefreshToken(), $calendar->getCalendarId(), $calendar->getMetaData() ?? []);
        }
        $this->updateCalendarMetaData($calendar, $result);
    }

    /**
     * @param string[] | null[] $metaData
     */
    private function updateCalendarMetaData(Calendar $calendar, array $metaData): void
    {
        $calendar->fillMetaData($metaData);
        $this->entityManager->persist($calendar);
        $this->entityManager->flush();
    }
}
