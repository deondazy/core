<?php

namespace Deondazy\Core\Base;

use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Deondazy\Core\Base\View;
use Twig\Error\RuntimeError;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Deondazy\Core\Config\Exceptions\FileNotFoundException;

class Controller
{
    public function __construct(protected View $view)
    {}

    /**
     * Render a view file template
     *
     * @param string $template
     * @param array $data
     *
     * @return Response
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws FileNotFoundException
     */
    protected function view(string $template, array $data = []): Response
    {
        return $this->view->render($template, $data);
    }
}
