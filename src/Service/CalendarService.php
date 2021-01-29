<?php


namespace App\Service;


use App\Repository\CalendarRepository;
use App\Repository\EventRepository;

class CalendarService
{
    /**
     * @var CalendarRepository
     */
    private CalendarRepository $calendarRepository;
    /**
     * @var EventRepository
     */
    private EventRepository $eventRepository;

    public function __construct(CalendarRepository $calendarRepository, EventRepository $eventRepository)
    {

        $this->calendarRepository = $calendarRepository;
        $this->eventRepository = $eventRepository;
    }

    public function getAllCalendars()
    {
        return $this->calendarRepository->findAll();
    }

    public function getAllEventsFromCalendar(int $id): ?array
    {
        return $this->eventRepository->findAllByCalendarId($id);
    }
}