<?php

namespace Deondazy\App\Controllers;

use Deondazy\Core\Base\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $this->render('index', ['title' => 'Deondazy Core']);
    }
}
