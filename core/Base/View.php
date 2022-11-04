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
        $loader = new FilesystemLoader(CORE_VIEWS);
        $twig = new Environment($loader, YamlConfig::load('twig'));
        $twig->addExtension(new DebugExtension());

        return $twig->render($template, $data);
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
