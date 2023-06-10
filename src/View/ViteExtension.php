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
        private string $devServer
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
            return $this->renderProductionAssets($assets);
        }
    }

    private function isOnDevServer(string $asset): bool 
    {
        static $devEntries = [];
    
        if (!isset($devEntries[$asset])) {
            $url = $this->devServer . '/' . $asset;
            $handle = curl_init($url);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_NOBODY, true);
    
            curl_exec($handle);
            $error = curl_errno($handle);
            curl_close($handle);
    
            $devEntries[$asset] = !$error;
        }
    
        return $devEntries[$asset];
    }

    private function getManifest(): array
    {
        static $manifest = null;

        if ($manifest === null) {
            $manifest = json_decode(
                file_get_contents($this->manifest),
                true
            );
        }

        return $manifest;
    }

    private function getUrlFromManifest(string $asset): string
    {
        $manifest = $this->getManifest();

        if (isset($manifest[$asset])) {
            $file = $manifest[$asset]['file'];
            return $this->config->get('app.url') . "/build/{$file}";
        }

        return '';
    }

    private function renderDevAssets(array $assets): string
    {
        $html = $this->getViteModule();

        foreach ($assets as $asset) {
            $url = $this->isOnDevServer($asset)
                ? "{$this->devServer}/{$asset}"
                : $this->getUrlFromManifest($asset);

            $html .= match ($this->getFileExtension($asset)) {
                'js' => $this->renderScriptTag($url),
                'css' => $this->renderLinkTag($url),
                default => '',
            };
        }

        return $html;
    }

    private function renderProductionAssets(array $assets): string
    {
        $html = '';

        foreach ($assets as $asset) {
            $url = $this->getUrlFromManifest($asset);

            $html .= match ($this->getFileExtension($asset)) {
                'js' => $this->renderScriptTag($url),
                'css' => $this->renderLinkTag($url),
                default => '',
            };
        }

        return $html;
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
        if ($this->isDev && $this->isOnDevServer('@vite/client')) {
            return "<script 
                type=\"module\"
                src=\"{$this->devServer}/@vite/client\">
                </script>";
        } else {
            return '';
        }
    }
}
