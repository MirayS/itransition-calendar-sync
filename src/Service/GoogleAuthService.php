<?php
declare(strict_types=1);

namespace App\Service;


use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoogleAuthService
{
    private \Google_Client $googleClient;

    public function __construct(\Google_Client $googleClient, UrlGeneratorInterface $router)
    {

        $this->googleClient = $googleClient;
        $this->googleClient->addScope(\Google_Service_Calendar::CALENDAR_READONLY);
        $this->googleClient->addScope(\Google_Service_Calendar::CALENDAR_EVENTS_READONLY);
        $this->googleClient->setAccessType("offline");
        $this->googleClient->setPrompt("consent");
        $this->googleClient->setIncludeGrantedScopes(true);
        $this->googleClient->setRedirectUri($router->generate("api.google.auth", [], UrlGeneratorInterface::ABSOLUTE_URL));
    }

    public function getAuthUrl(): string
    {
        return $this->googleClient->createAuthUrl();
    }

    public function getTokens(string $authCode): array
    {
        return $this->googleClient->fetchAccessTokenWithAuthCode($authCode);
    }

    public function validateToken(string $accessToken): bool
    {
        $this->googleClient->setAccessToken($accessToken);
        return $this->googleClient->isAccessTokenExpired();
    }

    public function getNewAccessToken(string $refreshToken): array
    {
        return $this->googleClient->refreshToken($refreshToken);
    }
}