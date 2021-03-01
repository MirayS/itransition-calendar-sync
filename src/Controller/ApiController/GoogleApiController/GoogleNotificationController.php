<?php

declare(strict_types=1);

namespace App\Controller\ApiController\GoogleApiController;

use App\Service\CalendarEntityService;
use App\Service\CalendarSynchronizationService;
use App\Service\GoogleService\GoogleCalendarEventsParser;
use App\Service\GoogleService\GoogleNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleNotificationController extends AbstractController
{
    private GoogleNotificationService $googleNotificationService;

    private CalendarEntityService $calendarService;

    public function __construct(GoogleNotificationService $googleNotificationService, CalendarEntityService $calendarService)
    {
        $this->googleNotificationService = $googleNotificationService;
        $this->calendarService = $calendarService;
    }

    /**
     * @Route("/api/google/notification", name="api.google.notification")
     */
    public function notificationGoogleCalendar(Request $request, GoogleCalendarEventsParser $googleCalendarEventsParser, CalendarSynchronizationService $calendarSynchronizationService): Response
    {
        $notificationId = $request->headers->get('X-Goog-Channel-Id');
        if (null == $notificationId) {
            throw $this->createNotFoundException();
        }

        $calendar = $this->calendarService->getCalendarByNotificationId($notificationId);
        if (null == $calendar) {
            return new Response();
        }

        $calendarSynchronizationService->syncCalendar($calendar);

        return new Response();
    }
}
