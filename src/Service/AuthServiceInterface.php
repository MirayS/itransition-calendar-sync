<?php
declare(strict_types=1);

namespace App\Service;


use App\Entity\AuthResult;

interface AuthServiceInterface
{
    public function getAuthUrl(): string;

    public function getTokens(string $authCode): AuthResult;

    public function isTokenValid(string $accessToken): bool;

    public function getNewAccessToken(string $refreshToken): AuthResult;

    public function setAccessToken(string $accessToken);
}