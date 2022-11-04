<?php

namespace Deondazy\Core\Base;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use Deondazy\Core\Config\YamlConfig;
use Deondazy\Core\Config\Exceptions\FileNotFoundException;

class View
{
    /**
     * Get the view file template using twig
     *
     * @param string $template
     * @param array $data
     *
     * @return string
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws FileNotFoundException
     */
    public function get(string $template, array $data = []): string
    {
        // If template has an extension, remove it
        if (strpos($template, '.') !== false) {
            $extension = explode('.', $template)[1];
            $templateName = trim(str_replace($extension, '', $template), '.');
        } else {
            // If template has no extension, use it
            $templateName = $template;
        }

        // Get the twig config file
        $config = YamlConfig::load('twig');

        // Get supported template extensions
        $extensions = $config['extensions'];

        // Find the file that matches the template name
        $templateFile = $this->findTemplateFile($templateName, $extensions);

        $loader = new FilesystemLoader(CORE_VIEWS);
        $twig = new Environment($loader, $config);
        $twig->addExtension(new DebugExtension());

        return $twig->render($templateFile, $data);
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
            if (file_exists(CORE_VIEWS . DS . $templateName . $extension)) {
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
     * @return void
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws FileNotFoundException
     */
    public function render(string $template, array $data = []): void
    {
        echo $this->get($template, $data);
    }
}
