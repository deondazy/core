<?php

declare(strict_types = 1);

namespace Deondazy\App\Controllers;

use Deondazy\Core\Base\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Deondazy\App\Services\UserAuthenticationService;

class AuthController extends Controller
{
    public function login(): ResponseInterface
    {
        return $this->view('auth/login');
    }

    public function showRegistrationForm(): ResponseInterface
    {
        return $this->view('auth.register');
    }

    public function register(
        ServerRequestInterface $request, 
        UserAuthenticationService $registrationService
    ): ResponseInterface
    {
        $formData = $request->getParsedBody();

        $registrationService->register($formData);

        return $this->redirect('/login');
    }

    public function forgotPassword(): ResponseInterface
    {
        return $this->view('auth/forgot-password');
    }
}
