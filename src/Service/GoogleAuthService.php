<?php
declare(strict_types=1);

namespace App\Service;


use App\Entity\AuthResult;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoogleAuthService implements AuthServiceInterface
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

    public function getTokens(string $authCode): AuthResult
    {
        $this->googleClient->fetchAccessTokenWithAuthCode($authCode);
        return new AuthResult($this->googleClient->getAccessToken()["access_token"], $this->googleClient->getRefreshToken());
    }

    public function isTokenValid(string $accessToken): bool
    {
        $this->googleClient->setAccessToken($accessToken);
        return $this->googleClient->isAccessTokenExpired();
    }

    public function getNewAccessToken(string $refreshToken): AuthResult
    {
        $this->googleClient->refreshToken($refreshToken);
        return new AuthResult($this->googleClient->getAccessToken()["access_token"], $this->googleClient->getRefreshToken());
    }

    public function getGoogleClient(): \Google_Client
    {
        return $this->googleClient;
    }

    public function setAccessToken(string $accessToken)
    {
        $this->googleClient->setAccessToken($accessToken);
    }
}