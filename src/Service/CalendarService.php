<?php
declare(strict_types=1);

namespace App\Service;


use App\Entity\Calendar;
use App\Repository\CalendarRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

class CalendarService
{
    private CalendarRepository $calendarRepository;
    private EventRepository $eventRepository;
    private EntityManagerInterface $entityManager;
    private GoogleCalendarService $googleCalendarService;

    public function __construct(CalendarRepository $calendarRepository, EventRepository $eventRepository, EntityManagerInterface $entityManager, GoogleCalendarService $googleCalendarService)
    {

        $this->calendarRepository = $calendarRepository;
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
        $this->googleCalendarService = $googleCalendarService;
    }

    public function getAllCalendars(): array
    {
        return $this->calendarRepository->findAll();
    }

    public function getOrCreateCalendar(string $calendarId, string $calendarName, string $accessToken, string $refreshToken): Calendar
    {
        $calendar = $this->calendarRepository->findOneBy(["calendarId" => $calendarId]);
        if ($calendar == null) {
            $calendar = new Calendar($calendarId, $calendarName, $accessToken, $refreshToken);
        } else {
            $calendar->setName($calendarName);
            $calendar->setAccessToken($accessToken);
            $calendar->setRefreshToken($refreshToken);
        }
        $this->entityManager->persist($calendar);
        $this->entityManager->flush();

        return $calendar;
    }

    public function getCalendar(int $id): Calendar
    {
        return $this->calendarRepository->find($id);
    }

    public function getCalendarByNotificationId(string $notificationId): ?Calendar
    {
        return $this->calendarRepository->findCalendarByNotificationChannelId($notificationId);
    }

    public function syncAllCalendars()
    {
        $calendars = $this->getAllCalendars();
        foreach ($calendars as $calendar) {
            $this->syncCalendar($calendar);
        }
    }

    public function syncCalendar(Calendar $calendar)
    {
        $this->googleCalendarService->parseEvents($calendar);
    }

    public function updateCalendar(Calendar $calendar)
    {
        $this->entityManager->persist($calendar);
        $this->entityManager->flush();
    }
}