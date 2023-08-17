<?php

declare(strict_types = 1);

namespace Denosys\Core\Base;

use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Denosys\Core\Base\View;
use Twig\Error\RuntimeError;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Denosys\Core\Config\Exceptions\FileNotFoundException;

class Controller
{
    public function __construct(
        protected View $view,
        private ContainerInterface $container
    ){

    }

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
     * Redirect to a given route
     *
     * @param string $route
     * @param array $headers
     *
     * @return Response
     */
    public function redirect(string $route, array $headers = []): Response
    {
        $response = $this->container->get(Response::class)->withStatus(302)
        ->withHeader('Location', $route);

        foreach ($headers as $name => $values) {
            foreach ((array) $values as $value) {
                $response = $response->withAddedHeader($name, $value);
            }
        }

        return $response;
    }
}
