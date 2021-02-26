<?php

declare(strict_types=1);

namespace App\Service\GoogleService;

use App\Service\AuthServiceInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoogleAuthService implements AuthServiceInterface
{
    private \Google_Client $googleClient;

    public function __construct(\Google_Client $googleClient, UrlGeneratorInterface $router)
    {

        $googleClient->addScope(\Google_Service_Calendar::CALENDAR_READONLY);
        $googleClient->addScope(\Google_Service_Calendar::CALENDAR_EVENTS_READONLY);
        $googleClient->setAccessType('offline');
        $googleClient->setPrompt('consent');
        $googleClient->setIncludeGrantedScopes(true);
        $googleClient->setRedirectUri($router->generate('api.google.auth', [], UrlGeneratorInterface::ABSOLUTE_URL));

        $this->googleClient = $googleClient;
    }

    public function getAuthUrl(): string
    {
        return $this->googleClient->createAuthUrl();
    }

    public function getRefreshToken(string $authCode): string
    {
        $this->googleClient->fetchAccessTokenWithAuthCode($authCode);

        return $this->googleClient->getRefreshToken();
    }

    public function getAccessToken(string $refreshToken): string
    {
        $this->googleClient->fetchAccessTokenWithRefreshToken($refreshToken);

        return $this->googleClient->getAccessToken()['access_token'];
    }
}
