<?php

namespace Deondazy\App\Controllers;

use Deondazy\Core\Base\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $this->render('index.html', ['title' => 'Deondazy Core']);
    }
}
