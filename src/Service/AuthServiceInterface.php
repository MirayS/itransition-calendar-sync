<?php

declare(strict_types=1);

namespace App\Service;

interface AuthServiceInterface
{
    public function getAuthUrl(): string;

    public function getRefreshToken(string $code): string;

    public function getAccessToken(string $refreshToken): string;
}
