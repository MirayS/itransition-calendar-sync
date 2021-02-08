<?php
declare(strict_types=1);

namespace App\Controller;


use App\Entity\Calendar;
use App\Service\CalendarService;
use App\Service\GoogleAuthService;
use App\Service\GoogleCalendarService;
use App\Service\GoogleNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GoogleApiController extends AbstractController
{
    /**
     * @Route("/api/google/oauth", name="api.google.auth")
     */
    public function googleAuth(GoogleAuthService $googleAuthService, Request $request): Response
    {
        $code = $request->query->get("code");
        return $this->render("api/oauth.html.twig", [
            "tokens" => $googleAuthService->getTokens($code)
        ]);
    }

    /**
     * @Route("/api/google/url", name="api.google.getUrl")
     */
    public function getGoogleAuthUrl(GoogleAuthService $googleAuthService): Response
    {
        return $this->json($googleAuthService->getAuthUrl());
    }

    /**
     * @Route("/api/google/calendars", name="api.google.calendars")
     */
    public function getGoogleCalendars(GoogleAuthService $googleAuthService, Request $request): Response
    {
        $accessToken = $request->query->get("accessToken");
        if ($accessToken == null)
            $this->createNotFoundException();
        $googleAuthService->setAccessToken($accessToken);
        $googleCalendarService = new GoogleCalendarService($googleAuthService->getGoogleClient());
        return $this->json($googleCalendarService->getCalendars());
    }

    /**
     * @Route("/api/google/new", name="api.google.new")
     */
    public function addNewGoogleCalendar(CalendarService $calendarService, Request $request): Response
    {
        $calendarId = $request->request->get("calendarId");
        $calendarName = $request->request->get("calendarName");
        $accessToken = $request->request->get("accessToken");
        $refreshToken = $request->request->get("refreshToken");

        $calendar = $calendarService->getOrCreateCalendar($calendarId, $calendarName, $accessToken, $refreshToken);
        $calendarService->syncCalendar($calendar);

        return $this->json(null);
    }

    /**
     * @Route("/api/google/notification", name="api.google.notification")
     */
    public function notificationGoogleCalendar(CalendarService $calendarService, Request $request) : Response
    {
        $notificationId = $request->headers->get("X-Goog-Channel-Id");
        $calendar = $calendarService->getCalendarByNotificationId($notificationId);
        if ($calendar != null) {
            $calendarService->syncCalendar($calendar);
        }

        return $this->json(null);
    }

    /**
     * @Route("/api/google/notification/{id}/start", name="api.google.notification.start")
     */
    public function startReceiveNotification(CalendarService $calendarService, GoogleNotificationService $googleNotificationService, int $id): Response
    {
        $calendar = $calendarService->getCalendar($id);
        $googleNotificationService->startReceiveNotification($calendar);

        return $this->json(null);
    }



    /**
     * @Route("/api/google/notification/{id}/stop", name="api.google.notification.stop")
     */
    public function stopReceiveNotification(CalendarService $calendarService, GoogleNotificationService $googleNotificationService, int $id): Response
    {
        $calendar = $calendarService->getCalendar($id);
        $googleNotificationService->stopReceiveNotification($calendar);

        return $this->json(null);
    }

}