<?php

declare(strict_types=1);

namespace App\Controller\ApiController\OutlookApiController;

use App\Service\CalendarEntityService;
use App\Service\OutlookService\OutlookNotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OutlookNotificationController extends AbstractController
{
    private OutlookNotificationService $outlookNotificationService;

    private CalendarEntityService $calendarEntityService;

    public function __construct(OutlookNotificationService $outlookNotificationService, CalendarEntityService $calendarEntityService)
    {
        $this->outlookNotificationService = $outlookNotificationService;
        $this->calendarEntityService = $calendarEntityService;
    }

    /**
     * @Route("/api/outlook/notification", name="api.outlook.notification")
     */
    public function notificationEndpoint(Request $request): Response
    {
        $validationToken = $request->get('validationToken');
        if (null != $validationToken) {
            return new Response($request->get('validationToken'));
        }

        return new Response('');
    }
}
