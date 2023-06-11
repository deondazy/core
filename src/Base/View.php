<?php

declare(strict_types = 1);

namespace Deondazy\Core\Base;

use Deondazy\Core\Config;
use Slim\Views\Twig;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Deondazy\Core\Config\Exceptions\FileNotFoundException;

class View
{
    public function __construct(
        protected Twig $twig,
        private ContainerInterface $container
    ) {}

    /**
     * Get the view file template using twig
     *
     * @param string $template
     * @param array $data
     *
     * @return string
     */
    public function get(string $template): string
    {
        if (strpos($template, '.') !== false) {
            $templateName = str_replace('.', '/', $template);
        } else {
            $templateName = $template;
        }

        $config = $this->container->get(Config::class)->get('views.twig');

        $extensions = $config['extensions'];

        return $this->findTemplateFile($templateName, $extensions);
    }

    /**
     * Find the template file
     *
     * @param string $templateName
     * @param array $extensions
     *
     * @return string
     *
     * @throws FileNotFoundException
     */
    private function findTemplateFile(string $templateName, array $extensions): string
    {
        foreach ($extensions as $extension) {
            if (file_exists(__DIR__ . '/../../app/Views/' . $templateName . $extension)) {
                return $templateName . $extension;
            }
        }

        throw new FileNotFoundException("No template file found for $templateName");
    }

    /**
     * Render the view file template using twig
     *
     * @param string $template
     * @param array $data
     *
     * @return Response
     *
     * @throws FileNotFoundException
     */
    public function render(string $template, array $data = []): Response
    {
        return $this->twig->render($this->container->get(Response::class), $this->get($template), $data);
    }

    /**
     * Redirect to a given route
     * 
     * @param string $route
     * @param array $data
     * 
     * @return Response
     */
    public function redirect(string $route): Response
    {
        return $this->container->get(Response::class)->withStatus(302)
            ->withHeader('Location', $route);
    }
}
