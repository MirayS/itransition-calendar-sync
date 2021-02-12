<?php

declare(strict_types=1);

namespace App\Controller\ApiController\BaseApiController;

use App\Service\CalendarEntityService;
use App\Service\GoogleService\GoogleCalendarEventsParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CalendarController extends AbstractController
{
    private CalendarEntityService $calendarEntityService;

    public function __construct(CalendarEntityService $calendarEntityService)
    {
        $this->calendarEntityService = $calendarEntityService;
    }

    /**
     * @Route("/api/calendars", name="api.calendars.get")
     */
    public function getAllCalendars(SerializerInterface $serializer): Response
    {
        $data = $this->calendarEntityService->getAllCalendars();

        $result = $serializer->serialize(
            $data,
            'json',
            ['groups' => 'list_calendar']
        );
        $response = new JsonResponse($result, 200, [], true);
        $response->setContentSafe();

        return $response;
    }

    /**
     * @Route("/api/calendars/sync", name="api.calendars.sync")
     */
    public function syncAllCalendars(CalendarEntityService $calendarService, GoogleCalendarEventsParser $googleCalendarEventsParser): Response
    {
        try {
            foreach ($calendarService->getAllCalendars() as $calendar) {
                $googleCalendarEventsParser->parseEvents($calendar);
            }

            return $this->json(null);
        } catch (\Exception $exception) {
            return $this->json($exception);
        }
    }

    /**
     * @Route("/api/calendars/{id}/sync", name="api.calendar.sync")
     */
    public function syncCalendar(CalendarEntityService $calendarService, GoogleCalendarEventsParser $googleCalendarEventsParser, int $id): Response
    {
        try {
            $calendar = $calendarService->getCalendar($id);
            if (null == $calendar) {
                throw $this->createNotFoundException();
            }

            $googleCalendarEventsParser->parseEvents($calendar);

            return $this->json(null);
        } catch (\Exception $exception) {
            return $this->json($exception);
        }
    }

    /**
     * @Route("/api/calendars/{id}/changeStatus", name="api.calendars.changeStatus")
     */
    public function changeCalendarStatus(SerializerInterface $serializer, CalendarEntityService $calendarService, int $id): Response
    {
        $calendar = $calendarService->getCalendar($id);
        if (null == $calendar) {
            throw $this->createNotFoundException();
        }

        $calendar->setIsShow(!$calendar->getIsShow());
        $calendarService->updateCalendar($calendar);

        $result = $serializer->serialize(
            $calendar,
            'json',
            ['groups' => 'show_calendar']
        );
        $response = new JsonResponse($result, 200, [], true);
        $response->setContentSafe();

        return $response;
    }
}
