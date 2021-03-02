<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Calendar;
use App\Service\GoogleService\GoogleCalendarEventsParser;
use App\Service\OutlookService\OutlookCalendarEventsParser;
use Doctrine\ORM\EntityManagerInterface;

class CalendarSynchronizationService
{
    private EventEntityService $eventEntityService;

    private GoogleCalendarEventsParser $googleCalendarEventsParser;

    private OutlookCalendarEventsParser $outlookCalendarEventsParser;

    private EntityManagerInterface $entityManager;

    public function __construct(EventEntityService $eventEntityService, GoogleCalendarEventsParser $googleCalendarEventsParser, OutlookCalendarEventsParser $outlookCalendarEventsParser, EntityManagerInterface $entityManager)
    {
        $this->eventEntityService = $eventEntityService;
        $this->googleCalendarEventsParser = $googleCalendarEventsParser;
        $this->outlookCalendarEventsParser = $outlookCalendarEventsParser;
        $this->entityManager = $entityManager;
    }

    public function syncCalendar(Calendar $calendar): void
    {
        $result = null;
        $syncToken = isset($calendar->getMetaData()[Calendar::SYNC_TOKEN_META]) ? $calendar->getMetaData()[Calendar::SYNC_TOKEN_META] : null;
        $refreshToken = $calendar->getRefreshToken();

        if (null == $refreshToken || '' == $refreshToken) {
            return;
        }

        if ('outlook' == $calendar->getCalendarType()) {
            $result = $this->outlookCalendarEventsParser->parseEvents($refreshToken, $calendar->getCalendarId(), $syncToken);
        } else {
            $result = $this->googleCalendarEventsParser->parseEvents($refreshToken, $calendar->getCalendarId(), $syncToken);
        }

        $this->eventEntityService->loadParsedEvents($result->getParsedEvents(), $calendar);

        $calendar->fillMetaData([
            Calendar::SYNC_TOKEN_META => $result->getNextSyncToken(),
        ]);
        $calendar->setLastSyncDate(new \DateTimeImmutable('now'));

        $this->entityManager->persist($calendar);
        $this->entityManager->flush();
    }
}
