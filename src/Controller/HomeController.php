<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\CalendarService;
use App\Service\GoogleAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(CalendarService $calendarService): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'calendars' => $calendarService->getAllCalendars()
        ]);
    }
}
