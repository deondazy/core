<?php

declare(strict_types = 1);

namespace Denosys\App\Controllers;

use Denosys\Core\Base\Controller;
use Psr\Http\Message\ResponseInterface;

class DashboardController extends Controller
{
    public function index(): ResponseInterface
    {
        return $this->view('dashboard.index');
    }

    public function profile(): ResponseInterface
    {
        return $this->view('dashboard.profile');
    }
}
