<?php

namespace Deondazy\App\Controllers;

use Deondazy\Core\Base\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController extends Controller
{
    public function login(Request $request, Response $response)
    {
        $this->render('auth/login');
        
        return $response;
    }

    public function register(Request $request, Response $response)
    {
        $this->render('auth/register');
        
        return $response;
    }
}
