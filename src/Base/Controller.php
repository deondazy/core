<?php

namespace Deondazy\Core\Base;

use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\SyntaxError;
use Deondazy\Core\Base\View;
use Twig\Error\RuntimeError;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Deondazy\Core\Config\Exceptions\FileNotFoundException;

class Controller
{
    public function __construct(
        protected Twig $twig,
        protected ServerRequestInterface $request,
        protected ResponseInterface $response
    ){}

    /**
     * Render a view file template
     *
     * @param string $template
     * @param array $data
     *
     * @return 
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws FileNotFoundException
     */
    protected function render(string $template, array $data = []): ResponseInterface
    {
        return $this->twig->render($this->response, $template, $data);
    }
}
