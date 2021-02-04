<?php
declare(strict_types=1);

namespace App\Controller;


use App\Entity\Calendar;
use App\Service\CalendarService;
use App\Service\GoogleAuthService;
use App\Service\GoogleCalendarService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GoogleApiController extends AbstractController
{
    /**
     * @Route("/api/google/oauth", name="api.google.auth")
     */
    public function googleAuth(GoogleAuthService $googleAuthService, Request $request)
    {
        $code = $request->query->get("code");
        return $this->render("api/oauth.html.twig", [
            "tokens" => $googleAuthService->getTokens($code)
        ]);
    }

    /**
     * @Route("/api/google/url", name="api.google.getUrl")
     */
    public function getGoogleAuthUrl(GoogleAuthService $googleAuthService)
    {
        return $this->json($googleAuthService->getAuthUrl());
    }

    /**
     * @Route("/api/google/calendars", name="api.google.calendars")
     */
    public function getGoogleCalendars(GoogleAuthService $googleAuthService, Request $request)
    {
        $accessToken = $request->query->get("accessToken");
        if ($accessToken == null)
            return $this->createNotFoundException();
        $googleAuthService->setAccessToken($accessToken);
        $googleCalendarService = new GoogleCalendarService($googleAuthService->getGoogleClient());
        return $this->json($googleCalendarService->getCalendars());
    }

    /**
     * @Route("/api/google/new", name="api.google.new")
     */
    public function addNewGoogleCalendar(CalendarService $calendarService, Request $request)
    {
        $calendarId = $request->request->get("calendarId");
        $calendarName = $request->request->get("calendarName");
        $accessToken = $request->request->get("accessToken");
        $refreshToken = $request->request->get("refreshToken");

        $calendar = $calendarService->getOrCreateCalendar($calendarId, $calendarName, $accessToken, $refreshToken);
        $calendarService->syncCalendar($calendar);

        return $this->json(null);
    }

}