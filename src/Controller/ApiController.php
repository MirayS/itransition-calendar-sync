<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\CalendarService;
use App\Service\EventService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/events", name="api.events.get")
     */
    public function getEvents(SerializerInterface $serializer, EventService $eventService, Request $request): Response
    {
        $minDate = $request->get('start') != null ? new \DateTime($request->get('start')) : new \DateTime('first day of this month');
        $maxDate = $request->get('end') != null ? new \DateTime($request->get('end')) : new \DateTime('last day of this month');

        $func = function ($item) {
            return [
                "title" => $item["name"],
                "start" => $item["startTime"],
                "end" => $item["endTime"],
                "allDay" => $item["isAllDay"],
                "description" => $item['description']
            ];
        };


        $result = array_map($func, $eventService->getAllEnabledEventsInRange($minDate, $maxDate));
        $result = $serializer->serialize(
            $result,
            'json',
            ['groups' => 'list_event']
        );
        $response = new JsonResponse($result, 200, [], true);

        $response->setContentSafe();

        return $response;
    }

    /**
     * @Route("/api/calendars", name="api.calendars.get")
     */
    public function getAllCalendars(SerializerInterface $serializer, CalendarService $calendarService): Response
    {
        $data = $calendarService->getAllCalendars();

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
    public function syncAllCalendars(CalendarService $calendarService): Response
    {
        try {
            $calendarService->syncAllCalendars();
            return $this->json(null);
        } catch (\Exception $exception) {
            return $this->json($exception);
        }

    }

    /**
     * @Route("/api/calendars/{id}/changeStatus", name="api.calendars.changeStatus")
     */
    public function changeCalendarStatus(SerializerInterface $serializer, CalendarService $calendarService, int $id): Response
    {
        $calendar = $calendarService->getCalendar($id);
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
