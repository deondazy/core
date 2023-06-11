<?php

declare(strict_types = 1);

namespace Deondazy\App\Controllers;

use Deondazy\Core\Base\Controller;
use Psr\Http\Message\ResponseInterface;

class DashboardController extends Controller
{
    public function index(): ResponseInterface
    {
        return $this->view('dashboard.index');
    }
}
