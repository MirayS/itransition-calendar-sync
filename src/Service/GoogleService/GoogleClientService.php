<?php

declare(strict_types=1);

namespace App\Service\GoogleService;

class GoogleClientService
{
    private \Google_Client $googleClient;

    private \Google_Service_Calendar $googleCalendarService;

    public function __construct(\Google_Client $googleClient)
    {
        $this->googleClient = $googleClient;
    }

    public function getGoogleClient(): \Google_Client
    {
        return $this->googleClient;
    }

    public function getGoogleCalendarClient(): \Google_Service_Calendar
    {
        if (!isset($this->googleCalendarService)) {
            $this->googleCalendarService = new \Google_Service_Calendar($this->googleClient);
        }

        return $this->googleCalendarService;
    }

    public function loadAccessToken(?string $refreshToken): void
    {
        if (null == $refreshToken) {
            throw new \Exception('Refresh token is null');
        }
        $this->googleClient->fetchAccessTokenWithRefreshToken($refreshToken);
    }
}
