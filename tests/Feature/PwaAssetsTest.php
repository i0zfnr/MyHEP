<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaAssetsTest extends TestCase
{
    public function test_service_worker_precache_assets_exist_in_public_directory(): void
    {
        $serviceWorker = file_get_contents(public_path('sw.js'));

        preg_match_all("/'(\/[^']+)'/", $serviceWorker, $matches);

        $precacheBlock = strstr($serviceWorker, 'self.addEventListener', true);
        $assets = array_values(array_unique(array_filter(
            $matches[1],
            fn (string $asset): bool => str_contains($precacheBlock, "'{$asset}'"),
        )));

        $this->assertNotEmpty($assets);

        foreach ($assets as $asset) {
            $path = parse_url($asset, PHP_URL_PATH);

            if ($path === '/') {
                continue;
            }

            $this->assertFileExists(public_path(ltrim($path, '/')), "Missing PWA pre-cache asset: {$asset}");
        }
    }

    public function test_manifest_defines_reachable_required_icons(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertNotEmpty($manifest['name'] ?? null);
        $this->assertNotEmpty($manifest['short_name'] ?? null);
        $this->assertSame('/', $manifest['start_url'] ?? null);
        $this->assertSame('standalone', $manifest['display'] ?? null);

        foreach ($manifest['icons'] ?? [] as $icon) {
            $path = parse_url($icon['src'], PHP_URL_PATH);
            $this->assertFileExists(public_path(ltrim($path, '/')), "Missing manifest icon: {$icon['src']}");
        }
    }
}
