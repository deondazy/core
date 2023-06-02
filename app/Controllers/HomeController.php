<?php

namespace Deondazy\App\Controllers;

use Deondazy\Core\Base\Controller;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HomeController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $this->render('index');
        
        return $response;
    }
}
