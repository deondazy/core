<?php

declare(strict_types = 1);

namespace Deondazy\Core\View;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class ViteExtension extends AbstractExtension
{
    public function __construct(
        private string $appUrl,
        private string $manifest,
        private string $viteServer
    ) {}

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'vite',
                [$this, 'getViteAssets'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function getViteAssets(array $assets): string
    {
        $html = $this->getViteClientModule();

        foreach ($assets as $asset) {
            if ($this->isOnViteServer($asset)) {
                $html .= $this->renderAssetFromViteServer($asset);
            } else {
                $html .= $this->renderAssetFromManifest($asset);
            }
        }

        return $html;
    }

    private function isOnViteServer(string $asset): bool 
    {
        static $assetEntries = [];
    
        if (!isset($assetEntries[$asset])) {
            $url = $this->getUrlFromViteServer($asset);
            $handle = curl_init($url);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_NOBODY, true);
    
            curl_exec($handle);
            $error = curl_errno($handle);
            curl_close($handle);
    
            $assetEntries[$asset] = !$error;
        }
    
        return $assetEntries[$asset];
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
            return $this->appUrl . "/build/{$file}";
        }

        return '';
    }

    private function getUrlFromViteServer(string $asset): string
    {
        return "{$this->viteServer}/{$asset}";
    }

    private function getFileExtension(string $filename): string
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        return strtolower($extension);
    }

    public function getViteClientModule(): string
    {
        if ($this->isOnViteServer('@vite/client')) {
            return "<script 
                type=\"module\"
                src=\"{$this->getUrlFromViteServer('/@vite/client')}\">
                </script>";
        } else {
            return '';
        }
    }

    private function renderAssetFromViteServer(string $asset): string
    {
        $url = $this->isOnViteServer($asset)
            ? $this->getUrlFromViteServer($asset)
            : $this->getUrlFromManifest($asset);

        $html = match ($this->getFileExtension($asset)) {
            'js' => $this->renderScriptTag($url),
            'css' => $this->renderLinkTag($url),
            default => '',
        };

        return $html;
    }

    private function renderAssetFromManifest(string $asset): string
    {   
        $url = $this->getUrlFromManifest($asset);

        $html = match ($this->getFileExtension($asset)) {
            'js' => $this->renderScriptTag($url),
            'css' => $this->renderLinkTag($url),
            default => '',
        };

        return $html;
    }

    private function renderScriptTag(string $src): string
    {
        return "<script type=\"module\" crossorigin src=\"$src\"></script>";
    }

    private function renderLinkTag(string $href): string
    {
        return "<link rel=\"stylesheet\" href=\"$href\">";
    }
}
