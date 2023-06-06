<?php

declare(strict_types = 1);

namespace Deondazy\App\Controllers;

use Deondazy\Core\Base\Controller;
use Psr\Http\Message\ResponseInterface;

class AuthController extends Controller
{
    public function login($username): ResponseInterface
    {
        return $this->view('auth/login', ['username' => $username]);
    }

    public function register(): ResponseInterface
    {
        return $this->view('auth/register');
    }
}
