<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CalendarRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CalendarRepository::class)
 */
class Calendar
{
    public const SUBSCRIPTION_ID_META = 'subscriptionId';
    public const SUBSCRIPTION_EXPIRATION_META = 'subscriptionExpiration';
    public const SYNC_TOKEN_META = 'syncToken';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"list_calendar"})
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"parse_calendar", "list_calendar"})
     */
    private string $name;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"list_calendar"})
     */
    private \DateTimeInterface $lastSyncDate;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"list_calendar"})
     */
    private bool $isShow;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"parse_calendar"})
     */
    private string $calendarId;

    /**
     * @ORM\OneToMany(targetEntity=Event::class, mappedBy="calendar", orphanRemoval=true)
     *
     * @var Collection<int, Event>
     */
    private Collection $events;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private string $refreshToken;

    /**
     * @ORM\Column(type="json", nullable=true)
     *
     * @var array<string, ?string>
     */
    private array $metaData = [];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $calendarType;

    public function __construct(string $calendarId, string $calendarName, string $refreshToken = '', string $calendarType = '')
    {
        $this->events = new ArrayCollection();
        $this->calendarId = $calendarId;
        $this->name = $calendarName;
        $this->refreshToken = $refreshToken;
        $this->lastSyncDate = new \DateTime();
        $this->isShow = true;
        $this->calendarType = $calendarType;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLastSyncDate(): ?\DateTimeInterface
    {
        return $this->lastSyncDate;
    }

    public function setLastSyncDate(\DateTimeInterface $lastSyncDate): self
    {
        $this->lastSyncDate = $lastSyncDate;

        return $this;
    }

    public function isShow(): bool
    {
        return $this->isShow;
    }

    public function hide(): self
    {
        $this->isShow = false;

        return $this;
    }

    public function show(): self
    {
        $this->isShow = true;

        return $this;
    }


    public function getCalendarId(): string
    {
        return $this->calendarId;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken ?? '';
    }

    /**
     * @return array<string, ?string>|null
     */
    public function getMetaData(): ?array
    {
        return $this->metaData;
    }

    /**
     * @param array<string, ?string> $newValues
     */
    public function fillMetaData(array $newValues): self
    {
        foreach ($newValues as $key => $value) {
            $this->metaData[$key] = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getCalendarType(): string
    {
        return $this->calendarType;
    }
}
