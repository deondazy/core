<?php

namespace Deondazy\App\Controllers;

use Deondazy\Core\Base\Controller;
use Psr\Http\Message\ResponseInterface;

class HomeController extends Controller
{
    public function index(): ResponseInterface
    {   
        return $this->render('index.html', ['home' => 'My Home Page']);
    }
}
