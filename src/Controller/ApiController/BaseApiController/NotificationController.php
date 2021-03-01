<?php

declare(strict_types=1);

namespace App\Controller\ApiController\BaseApiController;

use App\Service\CalendarEntityService;
use App\Service\CalendarNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    private CalendarNotificationService $calendarNotificationService;

    private CalendarEntityService $calendarEntityService;

    public function __construct(CalendarNotificationService $calendarNotificationService, CalendarEntityService $calendarEntityService)
    {
        $this->calendarNotificationService = $calendarNotificationService;
        $this->calendarEntityService = $calendarEntityService;
    }

    /**
     * @Route("/api/calendar/{id}/notification/subscribe", name="api.calendar.notification.subscribe")
     */
    public function startReceiveNotification(int $id): Response
    {
        $calendar = $this->calendarEntityService->getCalendar($id);
        if (null == $calendar) {
            throw $this->createNotFoundException();
        }

        $this->calendarNotificationService->subscribe($calendar);

        return new Response();
    }

    /**
     * @Route("/api/calendar/{id}/notification/stop", name="api.calendar.notification.stop")
     */
    public function stopReceiveNotification(int $id): Response
    {
        $calendar = $this->calendarEntityService->getCalendar($id);
        if (null == $calendar) {
            throw $this->createNotFoundException();
        }

        $this->calendarNotificationService->cancelSubscription($calendar);

        return $this->json(null);
    }

    /**
     * @Route("/api/calendar/{id}/notification/update", name="api.calendar.notification.update")
     */
    public function updateSubscription(int $id): Response
    {
        $calendar = $this->calendarEntityService->getCalendar($id);
        if (null == $calendar) {
            throw $this->createNotFoundException();
        }

        $this->calendarNotificationService->updateSubscription($calendar);

        return $this->json(null);
    }
}
