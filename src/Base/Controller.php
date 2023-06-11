<?php

declare(strict_types = 1);

namespace Deondazy\Core\Base;

use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Deondazy\Core\Base\View;
use Twig\Error\RuntimeError;
use Psr\Http\Message\ResponseInterface as Response;
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

    /**
     * Set a flash message
     * 
     * @param string $key
     * @param string $message
     * 
     * @return array|null
     */
    protected function flash(string $key, string $message = ''): array|null
    {
        return $this->view->flash($key, $message);
    }

    /**
     * Redirect to a given url
     *
     * @param string $url
     * @param array $data
     *
     * @return Response
     */
    protected function redirect(string $route): Response
    {
        return $this->view->redirect($route);
    }
}
