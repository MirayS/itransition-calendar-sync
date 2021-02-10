<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Calendar;
use App\Entity\Event;
use App\Repository\EventRepository;

class EventService
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function getOrCreateEvent(string $eventId, Calendar $calendar, string $name, \DateTime $start, \DateTime $end, bool $isAllDay, string $description): Event
    {
        $eventModel = $this->eventRepository->findOneBy(['eventId' => $eventId]);
        if (null != $eventModel) {
            $eventModel->setName($name);
            $eventModel->setStartTime($start);
            $eventModel->setEndTime($end);
            $eventModel->setIsAllDay($isAllDay);
            $eventModel->setDescription($description);
        } else {
            $eventModel = new Event($name, $eventId, $calendar, $start, $end, $isAllDay, $description);
        }

        return $eventModel;
    }

    public function getAllEnabledEventsInRange(\DateTime $start, \DateTime $end): ?array
    {
        return $this->eventRepository->findAllInRange($start, $end);
    }
}
