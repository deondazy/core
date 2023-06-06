<?php

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
        // If template has an extension, remove it
        if (strpos($template, '.') !== false) {
            $extension = explode('.', $template)[1];
            $templateName = trim(str_replace($extension, '', $template), '.');
        } else {
            // If template has no extension, use it
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
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws FileNotFoundException
     */
    public function render(string $template, array $data = []): Response
    {
        return $this->twig->render($this->response, $this->get($template), $data);
    }
}
