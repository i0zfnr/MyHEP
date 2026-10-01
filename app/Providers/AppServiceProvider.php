<?php

namespace App\Providers;

use Google\Client as GoogleClient;
use Google\Service\Drive;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Config as FlysystemConfig;
use League\Flysystem\Filesystem;
use League\Flysystem\Visibility;
use Masbug\Flysystem\GoogleDriveAdapter;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Storage::extend('google_drive', function ($app, array $config): FilesystemAdapter {
            foreach (['clientId', 'clientSecret', 'refreshToken', 'folderId'] as $key) {
                if (blank($config[$key] ?? null)) {
                    throw new RuntimeException('Google Drive backup configuration is incomplete.');
                }
            }

            $client = new GoogleClient;
            $client->setApplicationName(config('app.name', 'MyHEP').' Backup Service');
            $client->setScopes([Drive::DRIVE]);
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);

            $token = $client->fetchAccessTokenWithRefreshToken($config['refreshToken']);
            if (isset($token['error'])) {
                throw new RuntimeException('Google Drive authentication failed.');
            }

            $service = new Drive($client);
            $adapter = new GoogleDriveAdapter($service, null, [
                'sharedFolderId' => $config['folderId'],
                'useDisplayPaths' => true,
                // Retention must reclaim Drive quota; cleanup preserves the newest recovery point.
                'usePermanentDelete' => true,
            ]);
            $filesystem = new Filesystem($adapter, new FlysystemConfig([
                FlysystemConfig::OPTION_VISIBILITY => Visibility::PRIVATE,
            ]));

            return new FilesystemAdapter($filesystem, $adapter, $config);
        });
    }
}
