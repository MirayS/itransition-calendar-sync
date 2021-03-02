<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Calendar;
use App\Entity\Event;
use App\Repository\EventRepository;
use App\Service\Model\ParsedEvent;
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

    /**
     * @param ParsedEvent[] $parsedEvents
     */
    public function loadParsedEvents(array $parsedEvents, Calendar $calendar): void
    {
        foreach ($parsedEvents as $parsedEvent) {
            $eventModel = $this->eventRepository->findOneBy(['eventId' => $parsedEvent->getId()]);
            if ($parsedEvent->isRemoved()) {
                if (null != $eventModel) {
                    $this->entityManager->remove($eventModel);
                }
            } else {
                if (null == $eventModel) {
                    $eventModel = new Event($parsedEvent->getName() ?? '',
                        $parsedEvent->getId() ?? '',
                        $calendar, $parsedEvent->getStartTime() ?? new \DateTimeImmutable(),
                        $parsedEvent->getEndTime() ?? new \DateTimeImmutable(),
                        $parsedEvent->isAllDay() ?? false);
                } else {
                    $eventModel->setStartTime($parsedEvent->getStartTime() ?? new \DateTimeImmutable());
                    $eventModel->setEndTime($parsedEvent->getEndTime());
                    $eventModel->setIsAllDay($parsedEvent->isAllDay() ?? false);
                    $eventModel->setName($parsedEvent->getName() ?? '');
                    $eventModel->setDescription($parsedEvent->getDescription() ?? '');
                }
                $this->entityManager->persist($eventModel);
            }
        }
        $this->entityManager->flush();
    }
}
