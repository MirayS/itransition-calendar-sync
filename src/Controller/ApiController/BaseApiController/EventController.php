<?php

declare(strict_types=1);

namespace App\Controller\ApiController\BaseApiController;

use App\Service\EventEntityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class EventController extends AbstractController
{
    /**
     * @Route("/api/events", name="api.events.get")
     */
    public function getEvents(SerializerInterface $serializer, EventEntityService $eventService, Request $request): Response
    {
        $minDate = null != $request->get('start') ? new \DateTime($request->get('start')) : new \DateTime('first day of this month');
        $maxDate = null != $request->get('end') ? new \DateTime($request->get('end')) : new \DateTime('last day of this month');

        $events = $eventService->getAllEnabledEventsInRange($minDate, $maxDate);

        $result = $serializer->serialize(
            $events,
            'json',
            ['groups' => 'list_event']
        );
        $response = new JsonResponse($result, 200, [], true);

        $response->setContentSafe();

        return $response;
    }
}
