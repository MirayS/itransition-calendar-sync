<?php

declare(strict_types=1);

namespace App\Controller\ApiController\OutlookApiController;

use App\Service\CalendarEntityService;
use App\Service\CalendarSynchronizationService;
use App\Service\OutlookService\OutlookNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutlookNotificationController extends AbstractController
{
    private OutlookNotificationService $outlookNotificationService;

    private CalendarEntityService $calendarEntityService;

    private CalendarSynchronizationService $calendarSynchronizationService;

    public function __construct(OutlookNotificationService $outlookNotificationService, CalendarEntityService $calendarEntityService, CalendarSynchronizationService $calendarSynchronizationService)
    {
        $this->outlookNotificationService = $outlookNotificationService;
        $this->calendarEntityService = $calendarEntityService;
        $this->calendarSynchronizationService = $calendarSynchronizationService;
    }

    /**
     * @Route("/api/outlook/notification", name="api.outlook.notification", methods={"POST"})
     */
    public function notificationEndpoint(Request $request): Response
    {
        $validationToken = $request->get('validationToken');
        if (null != $validationToken) {
            return new Response($request->get('validationToken'));
        }

        $requestBody = json_decode($request->getContent(), true);
        $value = $requestBody['value'][0];
        $subscriptionId = $value['subscriptionId'];

        $calendar = $this->calendarEntityService->getCalendarByNotificationId($subscriptionId);
        if (null == $calendar) {
            return new Response();
        }

        $this->calendarSynchronizationService->syncCalendar($calendar);

        return new Response($subscriptionId);
    }
}
