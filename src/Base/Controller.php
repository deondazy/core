<?php

namespace Deondazy\Core\Base;

use Deondazy\Core\Base\View;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Deondazy\Core\Config\Exceptions\FileNotFoundException;

class Controller
{
    /**
     * The view instance
     *
     * @var View
     */
    private $view;

    public function __construct()
    {
        $this->view = new View();
    }

    /**
     * Render a view file template
     *
     * @param string $template
     * @param array $data
     *
     * @return void
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws FileNotFoundException
     */
    public function render(string $template, array $data = []): void
    {
        $this->view->render($template, $data);
    }
}
