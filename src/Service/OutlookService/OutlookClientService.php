<?php

declare(strict_types=1);

namespace App\Service\OutlookService;

use Microsoft\Graph\Graph;

class OutlookClientService
{
    private Graph $apiClient;

    private OutlookAuthService $authService;

    public function __construct(Graph $apiClient, OutlookAuthService $authService)
    {
        $this->apiClient = $apiClient;
        $this->authService = $authService;
    }

    public function getApiClient(): Graph
    {
        return $this->apiClient;
    }

    public function loadAccessToken(string $refreshToken): void
    {
        $accessToken = $this->authService->getAccessToken($refreshToken);
        $this->apiClient->setAccessToken($accessToken);
    }
}
