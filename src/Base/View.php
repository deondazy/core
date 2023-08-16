<?php

declare(strict_types = 1);

namespace Deondazy\Core\Base;

use Slim\Views\Twig;
use Odan\Session\SessionInterface;
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

        $config = $this->container->get('config')->get('views.twig');

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
     * Add a flash message
     * 
     * @param string $key
     * @param string $message
     * 
     * @return null|array
     */
    public function flash(string $key, string $message = ''): null|array
    {
        $session = $this->container->get(SessionInterface::class);

        if (empty($message) === false) {
            $session->getFlash()->add($key, $message);
            return null;
        }

        return $session->getFlash()->get($key);
    }
}
