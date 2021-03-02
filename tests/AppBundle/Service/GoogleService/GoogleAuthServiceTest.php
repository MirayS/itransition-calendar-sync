<?php

declare(strict_types=1);

namespace AppBundle\Service\GoogleService;

use App\Service\GoogleService\GoogleAuthService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoogleAuthServiceTest extends TestCase
{
    private GoogleAuthService $googleAuthService;
    private MockObject $googleClientMock;

    public function setUp(): void
    {
        $this->googleClientMock = $this->createMock(\Google_Client::class);
        $urlGeneratorMock = $this->createMock(UrlGeneratorInterface::class);

        $this->googleAuthService = new GoogleAuthService($this->googleClientMock, $urlGeneratorMock);
    }

    public function testGetAuthUrl()
    {
        $this->googleClientMock->method('createAuthUrl')->willReturn('testUrl');

        $result = $this->googleAuthService->getAuthUrl();

        $this->assertEquals('testUrl', $result);
    }

    public function testGetRefreshToken()
    {
        $authCode = 'testAuthCode';

        $this->googleClientMock->expects($this->once())->method('fetchAccessTokenWithAuthCode')->with($authCode);
        $this->googleClientMock->method('getRefreshToken')->willReturn($authCode);

        $refreshToken = $this->googleAuthService->getRefreshToken($authCode);

        $this->assertEquals($authCode, $refreshToken);
    }
}
