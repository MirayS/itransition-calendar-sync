<?php

declare(strict_types=1);

namespace App\Service\OutlookService;

use App\Service\AuthServiceInterface;
use League\OAuth2\Client\Grant\RefreshToken;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OutlookAuthService implements AuthServiceInterface
{
    private Microsoft $provider;

    public function __construct(string $clientId, string $clientSecret, UrlGeneratorInterface $urlGenerator)
    {
        $this->provider = new Microsoft([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $urlGenerator->generate('api.outlook.auth', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'urlAuthorize' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'urlAccessToken' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'urlResourceOwnerDetails' => 'https://outlook.office.com/api/v1.0/me',
            'defaultScopes' => [
                'https://graph.microsoft.com/Calendars.Read',
                'offline_access',
            ],
        ]);
    }

    public function getAuthUrl(): string
    {
        return $this->provider->getAuthorizationUrl();
    }

    public function getRefreshToken(string $code): string
    {
        $authResult = $this->provider->getAccessToken('authorization_code', ['code' => $code]);

        return $authResult->getRefreshToken();
    }

    public function getAccessToken(string $refreshToken): string
    {
        $grant = new RefreshToken();

        $accessToken = $this->provider->getAccessToken($grant, [
           'refresh_token' => $refreshToken,
           'type' => 'refresh_token',
        ]);

        return $accessToken->getToken();
    }
}
