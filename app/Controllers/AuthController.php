<?php

declare(strict_types=1);

namespace Denosys\App\Controllers;

use Denosys\Core\Base\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Denosys\App\Services\TokenStorageService;
use Denosys\App\Services\UserAuthenticationService;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthController extends Controller
{
    public function showLoginForm(): ResponseInterface
    {
        $message = $this->flash('error');

        return $this->view('auth.login', compact('message'));
    }

    public function login(
        ServerRequestInterface $request,
        UserAuthenticationService $authService
    ): ResponseInterface {
        $formData = $request->getParsedBody();

        try {
            $authService->login($formData);

            return $this->redirect('/dashboard');
        } catch (AuthenticationException $e) {
            $this->flash('error', $e->getMessage());
            return $this->redirect('/login');
        }
    }

    public function showRegistrationForm(): ResponseInterface
    {
        return $this->view('auth.register');
    }

    public function register(
        ServerRequestInterface $request,
        UserAuthenticationService $registrationService
    ): ResponseInterface {
        $formData = $request->getParsedBody();

        $registrationService->register($formData);

        return $this->redirect('/login');
    }

    public function forgotPassword(): ResponseInterface
    {
        return $this->view('auth/forgot-password');
    }

    public function logout(TokenStorageService $tokenStorageService): ResponseInterface
    {
        $tokenStorageService->logout();

        return $this->redirect('/');
    }
}
