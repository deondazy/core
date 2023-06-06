<?php

namespace Deondazy\App\Controllers;

use Deondazy\Core\Base\Controller;
use Psr\Http\Message\ResponseInterface;

class AuthController extends Controller
{
    public function login($username): ResponseInterface
    {
        return $this->render('auth/login.html', ['username' => $username]);
    }

    public function register(): ResponseInterface
    {
        return $this->render('auth/register.html');
    }
}
