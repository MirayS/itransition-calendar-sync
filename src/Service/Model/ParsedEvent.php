<?php

declare(strict_types=1);

namespace App\Service\Model;

class ParsedEvent
{
    private string $id;
    private bool $isRemoved;
    private ?string $name;
    private ?string $description;
    private ?\DateTimeImmutable $startTime;
    private ?\DateTimeImmutable $endTime;
    private ?bool $isAllDay;

    public function __construct(string $id,
                                bool $isRemoved,
                                ?string $name = null,
                                ?string $description = null,
                                ?\DateTimeImmutable $startTime = null,
                                ?\DateTimeImmutable $endTime = null,
                                ?bool $isAllDay = null)
    {
        $this->id = $id;
        $this->isRemoved = $isRemoved;
        $this->name = $name;
        $this->description = $description;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->isAllDay = $isAllDay;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isRemoved(): bool
    {
        return $this->isRemoved;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    public function getEndTime(): ?\DateTimeImmutable
    {
        return $this->endTime;
    }

    public function getIsAllDay(): ?bool
    {
        return $this->isAllDay;
    }
}
