<?php

declare(strict_types=1);

namespace AppBundle\Service\GoogleService;

use App\Service\GoogleService\GoogleClientService;
use PHPUnit\Framework\TestCase;

class GoogleClientServiceTest extends TestCase
{
    private GoogleClientService $googleClientService;

    public function setUp(): void
    {
        $googleClientMock = $this->getMockBuilder(\Google_Client::class)->getMock();
        $this->googleClientService = new GoogleClientService($googleClientMock);
    }

    public function testGetGoogleClient()
    {
        $this->assertInstanceOf(\Google_Client::class, $this->googleClientService->getGoogleClient());
    }

    public function testGetGoogleCalendarClient()
    {
        $this->assertInstanceOf(\Google_Service_Calendar::class, $this->googleClientService->getGoogleCalendarClient());
    }
}
