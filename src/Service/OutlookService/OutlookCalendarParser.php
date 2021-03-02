<?php

declare(strict_types=1);

namespace App\Service\OutlookService;

use App\Entity\Calendar;
use App\Service\CalendarParserInterface;

class OutlookCalendarParser implements CalendarParserInterface
{
    private OutlookClientService $clientService;

    public function __construct(OutlookClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function parseCalendars(string $refreshToken): array
    {
        $this->clientService->loadAccessToken($refreshToken);
        $calendarsObject = $this->getCalendarsFromApi();
        return $this->parseCalendarsFromCalendarsObject($calendarsObject);
    }

    /**
     * @return \Microsoft\Graph\Model\Calendar[]
     */
    private function getCalendarsFromApi(): array
    {
        $apiClient = $this->clientService->getApiClient();
        $requestResult = $apiClient->createRequest('GET', '/me/calendars')->execute();
        $resultObject = $requestResult->getResponseAsObject(\Microsoft\Graph\Model\Calendar::class);

        return $resultObject;
    }

    /**
     * @param \Microsoft\Graph\Model\Calendar[] $calendarsObject
     * @return Calendar[]
     */
    private function parseCalendarsFromCalendarsObject(array $calendarsObject): array
    {
        $calendars = [];
        foreach ($calendarsObject as $calendarObject) {
            $calendars[] = new Calendar($calendarObject->getId(), $calendarObject->getName());
        }
        return $calendars;
    }
}
