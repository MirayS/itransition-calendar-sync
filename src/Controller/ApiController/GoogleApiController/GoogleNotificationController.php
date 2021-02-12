<?php

declare(strict_types=1);

namespace App\Controller\ApiController\GoogleApiController;

use App\Service\CalendarEntityService;
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
    public function notificationGoogleCalendar(Request $request, GoogleCalendarEventsParser $googleCalendarEventsParser): Response
    {
        $notificationId = $request->headers->get('X-Goog-Channel-Id');
        if (null == $notificationId) {
            throw $this->createNotFoundException();
        }

        $calendar = $this->calendarService->getCalendarByNotificationId($notificationId);
        if (null == $calendar) {
            throw $this->createNotFoundException();
        }

        $googleCalendarEventsParser->parseEvents($calendar);

        return $this->json(null);
    }

    /**
     * @Route("/api/google/notification/{id}/start", name="api.google.notification.start")
     */
    public function startReceiveNotification(int $id): Response
    {
        $calendar = $this->calendarService->getCalendar($id);
        if (null == $calendar) {
            throw $this->createNotFoundException();
        }

        $this->googleNotificationService->startReceiveNotification($calendar);

        return $this->json(null);
    }

    /**
     * @Route("/api/google/notification/{id}/stop", name="api.google.notification.stop")
     */
    public function stopReceiveNotification(int $id): Response
    {
        $calendar = $this->calendarService->getCalendar($id);
        if (null == $calendar) {
            throw $this->createNotFoundException();
        }

        $this->googleNotificationService->stopReceiveNotification($calendar);

        return $this->json(null);
    }
}
