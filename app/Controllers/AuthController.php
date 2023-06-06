<?php

declare(strict_types = 1);

namespace Deondazy\App\Controllers;

use Deondazy\Core\Base\Controller;
use Psr\Http\Message\ResponseInterface;

class AuthController extends Controller
{
    public function login(): ResponseInterface
    {
        return $this->view('auth/login');
    }

    public function register(): ResponseInterface
    {
        return $this->view('auth/register');
    }
}
