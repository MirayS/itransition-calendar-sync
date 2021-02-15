<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Calendar;
use App\Repository\CalendarRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

class CalendarEntityService
{
    private CalendarRepository $calendarRepository;
    private EventRepository $eventRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(CalendarRepository $calendarRepository, EventRepository $eventRepository, EntityManagerInterface $entityManager)
    {
        $this->calendarRepository = $calendarRepository;
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @return Calendar[]
     */
    public function getAllCalendars(): array
    {
        return $this->calendarRepository->findAll();
    }

    public function getOrCreateCalendar(string $calendarId, string $calendarName, string $refreshToken): Calendar
    {
        $calendar = $this->calendarRepository->findOneBy(['calendarId' => $calendarId]);
        if (null == $calendar) {
            $calendar = new Calendar($calendarId, $calendarName, $refreshToken);
        } else {
            $calendar->setName($calendarName);
            $calendar->setRefreshToken($refreshToken);
        }
        $this->entityManager->persist($calendar);
        $this->entityManager->flush();

        return $calendar;
    }

    public function getCalendar(int $id): ?Calendar
    {
        return $this->calendarRepository->find($id);
    }

    public function getCalendarByNotificationId(string $notificationId): ?Calendar
    {
        return $this->calendarRepository->findByNotificationChannelId($notificationId);
    }

    public function updateCalendar(Calendar $calendar): void
    {
        $this->entityManager->persist($calendar);
        $this->entityManager->flush();
    }
}
