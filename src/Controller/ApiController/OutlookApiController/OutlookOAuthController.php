<?php

declare(strict_types=1);

namespace App\Controller\ApiController\OutlookApiController;

use App\Service\OutlookService\OutlookAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class OutlookOAuthController extends AbstractController
{
    private OutlookAuthService $authService;

    public function __construct(OutlookAuthService $googleAuthService)
    {
        $this->authService = $googleAuthService;
    }

    /**
     * @Route("/api/outlook/oauth", name="api.outlook.auth")
     */
    public function auth(Request $request, SessionInterface $session): Response
    {
        $code = $request->query->get('code');
        if (null == $code) {
            throw $this->createNotFoundException();
        }

        $refreshToken = $this->authService->getRefreshToken($code);
        if (null == $refreshToken) {
            throw new \Exception('Invalid code', 401);
        }

        $session->set('refreshToken', $refreshToken);

        return $this->render('api/oauth.html.twig');
    }

    /**
     * @Route("/api/outlook/url", name="api.outlook.getUrl")
     */
    public function getAuthUrl(): Response
    {
        return $this->json($this->authService->getAuthUrl());
    }
}
