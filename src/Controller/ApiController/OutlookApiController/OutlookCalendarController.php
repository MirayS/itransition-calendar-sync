<?php

declare(strict_types=1);

namespace App\Controller\ApiController\OutlookApiController;

use App\Service\CalendarEntityService;
use App\Service\GoogleService\GoogleCalendarEventsParser;
use App\Service\GoogleService\GoogleCalendarParser;
use App\Service\OutlookService\OutlookCalendarParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class OutlookCalendarController extends AbstractController
{
    /**
     * @Route("/api/outlook/calendars", name="api.outlook.calendars")
     */
    public function getCalendars(OutlookCalendarParser $calendarService, SessionInterface $session, SerializerInterface $serializer): Response
    {
        $refreshToken = $session->get('refreshToken');
        if (null == $refreshToken) {
            throw $this->createNotFoundException();
        }

        $result = $serializer->serialize(
            $calendarService->parseCalendars($refreshToken),
            'json',
            ['groups' => 'parse_calendar']
        );
        $response = new JsonResponse($result, 200, [], true);
        $response->setContentSafe();

        return $response;
    }
}
