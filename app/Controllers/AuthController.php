<?php

namespace Deondazy\App\Controllers;

use Deondazy\Core\Base\Controller;

class AuthController extends Controller
{
    public function login($username)
    {
        return $this->render('auth/login.html', ['username' => $username]);
    }

    public function register()
    {
        return $this->render('auth/register.html');
    }
}
