<?php

namespace App\Controller;

use App\Service\CalendarService;
use App\Service\GoogleAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/api", name="api")
     */
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    /**
     * @Route("/api/google/data", name="api.google.data")
     */
    public function getGoogleApiData(): Response
    {
        return $this->json([
            "api_key" => $this->getParameter("app.google.apiKey"),
            "client_id" => $this->getParameter("app.google.clientId"),
        ]);
    }

    /**
     * @Route("/api/calendars/{id}/events", name="api.calendars.get")
     */
    public function getEvents(CalendarService $calendarService, SerializerInterface $serializer, int $id): Response
    {
        $result = $serializer->serialize(
            $calendarService->getAllEventsFromCalendar($id),
            'json',
            ['groups' => 'list_event']
        );
        $response = new JsonResponse($result, 200, [], true);

        $response->setContentSafe();

        return $response;
    }

    /**
     * @Route("/api/google/oauth", name="api.google.auth")
     */
    public function googleAuth(GoogleAuthService $googleAuthService, Request $request)
    {
        $code = $request->query->get("code");
        return $this->json($googleAuthService->getTokens($code));
    }


    /**
     * @Route("/api/google/url", name="api.google.getUrl")
     */
    public function getGoogleAuthUrl(GoogleAuthService $googleAuthService)
    {
        return$this->json($googleAuthService->getAuthUrl());
    }
}
