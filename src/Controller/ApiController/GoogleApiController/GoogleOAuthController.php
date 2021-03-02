<?php

declare(strict_types=1);

namespace App\Controller\ApiController\GoogleApiController;

use App\Service\GoogleService\GoogleAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class GoogleOAuthController extends AbstractController
{
    private GoogleAuthService $googleAuthService;

    public function __construct(GoogleAuthService $googleAuthService)
    {
        $this->googleAuthService = $googleAuthService;
    }

    /**
     * @Route("/api/google/oauth", name="api.google.auth")
     */
    public function googleAuth(Request $request, SessionInterface $session): Response
    {
        $code = $request->query->get('code');
        if (null == $code) {
            throw $this->createNotFoundException();
        }

        $refreshToken = $this->googleAuthService->getRefreshToken($code);
        if (null == $refreshToken) {
            throw new \Exception('Invalid code', 401);
        }

        $session->set('refreshToken', $refreshToken);
        $session->set('type', 'google');

        return $this->render('api/oauth.html.twig');
    }

    /**
     * @Route("/api/google/url", name="api.google.getUrl")
     */
    public function getGoogleAuthUrl(): Response
    {
        return $this->json($this->googleAuthService->getAuthUrl());
    }
}
