<?php

declare(strict_types = 1);

namespace Denosys\Core\View;

use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Denosys\Core\Exceptions\ViteManifestNotFoundException;

class ViteExtension extends AbstractExtension
{
    public function __construct(
        private string $buildPath,
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
            $html .= $this->isOnViteServer($asset)
                ? $this->renderAssetFromViteServer($asset)
                : $this->renderAssetFromManifest($asset);
        }

        return $html;
    }

    private function isOnViteServer(string $asset): bool 
    {
        static $assetEntries = [];

        return $assetEntries[$asset] ??= $this->checkAssetOnViteServer($asset);
    }

    private function checkAssetOnViteServer(string $asset): bool
    {
        $url = $this->getUrlFromViteServer($asset);
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_NOBODY, true);

        curl_exec($handle);
        $error = curl_errno($handle);
        curl_close($handle);

        return !$error;
    }

    private function getManifest(): array
    {
        static $manifest = null;

        return $manifest ??= $this->loadManifest();
    }

    private function loadManifest(): array
    {
        if (!is_file($this->manifest)) {
            throw new ViteManifestNotFoundException(
                "Vite manifest not found at: $this->manifest"
            );
        }

        return json_decode(file_get_contents($this->manifest), true);
    }

    private function getUrlFromManifest(string $asset): string
    {
        return $this->buildPath . '/' . ($this->getManifest()[$asset]['file'] ?? '');
    }

    private function getUrlFromViteServer(string $asset): string
    {
        return "{$this->viteServer}/{$asset}";
    }

    private function renderAsset(string $asset, string $url): string
    {
        return match (pathinfo($asset, PATHINFO_EXTENSION)) {
            'js', 'mjs' => $this->renderScriptTag($url),
            'css', 'scss' => $this->renderLinkTag($url),
            default => '',
        };
    }

    private function renderAssetFromViteServer(string $asset): string
    {
        return $this->renderAsset($asset, $this->getUrlFromViteServer($asset));
    }

    private function renderAssetFromManifest(string $asset): string
    {
        return $this->renderAsset($asset, $this->getUrlFromManifest($asset));
    }

    private function renderScriptTag(string $src): string
    {
        return "<script type=\"module\" crossorigin src=\"$src\"></script>";
    }

    private function renderLinkTag(string $href): string
    {
        return "<link rel=\"stylesheet\" href=\"$href\">";
    }

    public function getViteClientModule(): string
    {
        return $this->isOnViteServer('@vite/client')
            ? $this->renderScriptTag($this->getUrlFromViteServer('@vite/client'))
            : '';
    }
}
