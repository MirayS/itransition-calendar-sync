<?php

declare(strict_types=1);

namespace App\Service\Model;

class EventsParserResult
{
    private ?string $nextSyncToken;
    /**
     * @var ParsedEvent[]
     */
    private array $parsedEvents;

    /**
     * @param ParsedEvent[] $parsedEvents
     */
    public function __construct(?string $nextSyncToken, array $parsedEvents)
    {
        $this->nextSyncToken = $nextSyncToken;
        $this->parsedEvents = $parsedEvents;
    }

    /**
     * @return ParsedEvent[]
     */
    public function getParsedEvents(): array
    {
        return $this->parsedEvents;
    }

    public function getNextSyncToken(): ?string
    {
        return $this->nextSyncToken;
    }
}
