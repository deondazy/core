<?php

declare(strict_types = 1);

namespace Deondazy\Core\View;

use Twig\TwigFunction;
use Deondazy\Core\Config;
use Twig\Extension\AbstractExtension;

class ViteExtension extends AbstractExtension
{
    public function __construct(
        private Config $config,
        private bool $isDev,
        private string $manifest,
        private string $devServerHost,
        private int $devServerPort
    ) {}

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'vite',
                [$this, 'getViteAsset'],
                ['is_safe' => ['html']]
        ),
        ];
    }

    public function getViteAsset(array $assets): string
    {
        if ($this->isDev) {
            return $this->renderDevAssets($assets);
        } else {
            $manifest = json_decode(
                file_get_contents($this->manifest),
                true
            );

            $html = '';
            foreach ($assets as $asset) {
                $html .= $this->renderProductionAsset($asset, $manifest);
            }

            return $html;
        }
    }

    private function renderDevAssets(array $assets): string
    {
        $html = $this->getViteModule();

        foreach ($assets as $asset) {
            $html .= match ($this->getFileExtension($asset)) {
                'js' => $this->renderScriptTag("http://{$this->devServerHost}:{$this->devServerPort}/$asset"),
                'css' => $this->renderLinkTag("http://{$this->devServerHost}:{$this->devServerPort}/$asset"),
                default => '',
            };
        }

        return $html;
    }

    private function renderProductionAsset(string $asset, array $manifest): string
    {
        if (isset($manifest[$asset])) {
            $file = $manifest[$asset]['file'];
            $url = $this->config->get('app.url') . "/build/$file";

            return match ($this->getFileExtension($asset)) {
                'js' => $this->renderScriptTag($url),
                'css' => $this->renderLinkTag($url),
                default => '',
            };
        }

        return '';
    }

    private function getFileExtension(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return strtolower($extension);
    }

    private function renderScriptTag(string $src): string
    {
        return "<script type=\"module\" crossorigin src=\"$src\"></script>";
    }

    private function renderLinkTag(string $href): string
    {
        return "<link rel=\"stylesheet\" href=\"$href\">";
    }

    public function getViteModule(): string
    {
        if ($this->isDev) {
            return "<script 
                type=\"module\"
                src=\"http://{$this->devServerHost}:{$this->devServerPort}/@vite/client\">
                </script>";
        } else {
            return '';
        }
    }
}
