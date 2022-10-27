<?php

namespace Deondazy\App\Controllers;

use Deondazy\Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $this->view('index.html', ['title' => 'Deondazy Core']);
    }
}
