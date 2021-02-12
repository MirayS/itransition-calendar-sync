<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Calendar;
use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;

class EventEntityService
{
    private EventRepository $eventRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(EventRepository $eventRepository, EntityManagerInterface $entityManager)
    {
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
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

    /**
     * @return Event[]|null
     */
    public function getAllEnabledEventsInRange(\DateTime $start, \DateTime $end): ?array
    {
        return $this->eventRepository->findAllInDateTimeRange($start, $end);
    }

    public function updateEvent(Event $event, bool $saveChanges = true): void
    {
        $this->entityManager->persist($event);
        if ($saveChanges) {
            $this->saveChanges();
        }
    }

    public function removeEvent(Event $event, bool $saveChanges = true): void
    {
        $this->entityManager->remove($event);
        if ($saveChanges) {
            $this->saveChanges();
        }
    }

    public function saveChanges(): void
    {
        $this->entityManager->flush();
    }
}
