<?php

namespace Deondazy\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Controller
{
    public $twig;

    public function __construct()
    {
        $loader = new FilesystemLoader(CORE_ROOT . '/app/Views');
        $this->twig = new Environment($loader, [
            'cache' => CORE_ROOT . '/cache',
            'debug' => true,
        ]);
    }

    public function view($view, $data = [])
    {
        echo $this->twig->render($view, $data);
    }
}
