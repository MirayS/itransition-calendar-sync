<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 */
class Event
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"show_calendar", "list_event"})
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $eventId;

    /**
     * @ORM\ManyToOne(targetEntity=Calendar::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Calendar $calendar;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"show_calendar", "list_event"})
     */
    private \DateTimeInterface $startTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"show_calendar", "list_event"})
     */
    private ?\DateTimeInterface $endTime;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"show_calendar", "list_event"})
     */
    private string $name;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"show_calendar", "list_event"})
     */
    private bool $isAllDay;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    public function __construct(string $name, string $eventId, Calendar $calendar, \DateTimeInterface $startTime, ?\DateTimeInterface $endTime = null, bool $isAllDay = true, string $description = "")
    {
        $this->name = $name;
        $this->eventId = $eventId;
        $this->calendar = $calendar;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->isAllDay = $isAllDay;
        $this->description = $description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventId(): ?string
    {
        return $this->eventId;
    }

    public function setEventId(string $eventId): self
    {
        $this->eventId = $eventId;

        return $this;
    }

    public function getCalendar(): ?Calendar
    {
        return $this->calendar;
    }

    public function setCalendar(?Calendar $calendar): self
    {
        $this->calendar = $calendar;

        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): self
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(?\DateTimeInterface $endTime): self
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIsAllDay(): ?bool
    {
        return $this->isAllDay;
    }

    public function setIsAllDay(bool $isAllDay): self
    {
        $this->isAllDay = $isAllDay;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
