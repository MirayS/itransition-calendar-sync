<?php
declare(strict_types=1);

namespace App\Entity;


class AuthResult
{
    private string $accessToken;
    private string $refreshToken;

    public function __construct(string $accessToken, string $refreshToken)
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }
}