<?php

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetVersionExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('asset_version', [$this, 'assetVersion']),
        ];
    }

    /**
     * Appends ?v=<filemtime> to a local asset path for cache busting.
     * Falls back to the raw path if the file doesn't exist on disk.
     */
    public function assetVersion(string $path): string
    {
        $diskPath = ltrim($path, '/');
        if (file_exists($diskPath)) {
            return $path . '?v=' . filemtime($diskPath);
        }
        return $path;
    }
}
