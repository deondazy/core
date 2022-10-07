<?php 

namespace Deondazy\Core\Controllers;

class HomepageController extends Controller
{
    public function index()
    {
        $this->view('index.html', ['title' => 'Deondazy Core']);
    }
}