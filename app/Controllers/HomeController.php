<?php

declare(strict_types=1);

namespace Denosys\App\Controllers;

use Denosys\Core\Base\Controller;
use Psr\Http\Message\ResponseInterface;

class HomeController extends Controller
{
    public function index(): ResponseInterface
    {
        return $this->view('index');
    }
}
