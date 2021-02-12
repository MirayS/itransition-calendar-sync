<?php

declare(strict_types=1);

namespace App\Service\GoogleService;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoogleAuthService
{
    private GoogleClientService $googleClientService;

    public function __construct(GoogleClientService $googleClientService, UrlGeneratorInterface $router)
    {
        $this->googleClientService = $googleClientService;

        $googleClient = $this->googleClientService->getGoogleClient();
        $googleClient->addScope(\Google_Service_Calendar::CALENDAR_READONLY);
        $googleClient->addScope(\Google_Service_Calendar::CALENDAR_EVENTS_READONLY);
        $googleClient->setAccessType('offline');
        $googleClient->setPrompt('consent');
        $googleClient->setIncludeGrantedScopes(true);
        $googleClient->setRedirectUri($router->generate('api.google.auth', [], UrlGeneratorInterface::ABSOLUTE_URL));
    }

    public function getAuthUrl(): string
    {
        return $this->googleClientService->getGoogleClient()->createAuthUrl();
    }

    public function getRefreshToken(string $authCode): ?string
    {
        $this->googleClientService->getGoogleClient()->fetchAccessTokenWithAuthCode($authCode);

        return $this->googleClientService->getGoogleClient()->getRefreshToken();
    }
}
