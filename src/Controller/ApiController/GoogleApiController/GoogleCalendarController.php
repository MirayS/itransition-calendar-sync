<?php

declare(strict_types=1);

namespace App\Controller\ApiController\GoogleApiController;

use App\Service\CalendarEntityService;
use App\Service\GoogleService\GoogleCalendarEventsParser;
use App\Service\GoogleService\GoogleCalendarParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class GoogleCalendarController extends AbstractController
{
    /**
     * @Route("/api/google/calendars", name="api.google.calendars")
     */
    public function getGoogleCalendars(GoogleCalendarParser $googleCalendarService, SessionInterface $session): Response
    {
        $refreshToken = $session->get('refreshToken');
        if (null == $refreshToken) {
            throw $this->createNotFoundException();
        }

        return $this->json($googleCalendarService->getCalendars($refreshToken));
    }

    /**
     * @Route("/api/google/new", name="api.google.new")
     */
    public function addNewGoogleCalendar(CalendarEntityService $calendarService, Request $request, GoogleCalendarEventsParser $googleCalendarEventsParser, SessionInterface $session): Response
    {
        $refreshToken = $session->get('refreshToken');

        $calendarId = $request->request->get('calendarId');
        $calendarName = $request->request->get('calendarName');

        $calendar = $calendarService->getOrCreateCalendar($calendarId, $calendarName, $refreshToken);
        $googleCalendarEventsParser->parseEvents($calendar);

        return $this->json(null);
    }
}
