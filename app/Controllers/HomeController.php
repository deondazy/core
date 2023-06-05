<?php

namespace Deondazy\App\Controllers;

use Deondazy\Core\Base\Controller;

class HomeController extends Controller
{
    public function index()
    {   
        return $this->render('index.html', ['home' => 'My Home Page']);
    }
}
