<?php

declare(strict_types = 1);

namespace Deondazy\Core\Base;

use Deondazy\Core\Config;
use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Deondazy\Core\Config\Exceptions\FileNotFoundException;

class View
{
    public function __construct(
        protected Twig $twig,
        protected Request $request,
        protected Response $response,
        private Config $config
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

        $config = $this->config->get('views.twig');

        // Get supported template extensions
        $extensions = $config['extensions'];

        // Find the file that matches the template name
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
        return $this->twig->render($this->response, $this->get($template), $data);
    }

    /**
     * Redirect to a given route
     * 
     * @param string $route
     * @param array $data
     * 
     * @return Response
     */
    public function redirect(string $route, array $data = []): Response
    {
        return $this->response->withHeader('Location', $route)->withStatus(302);
    }
}