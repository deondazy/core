<?php

declare(strict_types = 1);

namespace Deondazy\Core\View;

use Twig\TwigFunction;
use Deondazy\Core\Config;
use Twig\Extension\AbstractExtension;

class ViteExtension extends AbstractExtension
{

    public function __construct(
        private bool $isDev,
        private array $manifest,
        private Config $config)
    {
        if (!$this->isDev) {
            $this->manifest = json_decode(
                file_get_contents(
                    $config->get('paths.public_dir') . '/build/manifest.json'
                ), true
            );
        }
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('vite_asset', [$this, 'getViteAsset']),
        ];
    }

    public function getViteAsset(string $asset): string
    {
        if ($this->isDev) {
            // Development mode: return the asset from the Vite development server
            return "http://127.0.0.1:4000/$asset";
        } else {
            // Production mode: use the manifest file to get the asset URL
            if (isset($this->manifest[$asset])) {
                return "/build/{$this->manifest[$asset]}";
            } else {
                return "/build/$asset"; // Fallback if the asset is not found in the manifest
            }
        }
    }
}
