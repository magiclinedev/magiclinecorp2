<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\Filesystem as Flysystem;

use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Kunnu\Dropbox\DropboxGuzzleHttpClient;

use GuzzleHttp\Client as GuzzleClient;

use Illuminate\Support\Facades\Gate;

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
        // admin 1 and owner
        Gate::define('super_admin', function ($user) {
            return in_array($user->status, [1, 4]);
        });

        //dropbox
        Storage::extend('dropbox', function (Application $app, array $config) {
            // Create a DropboxApp instance with your app credentials
            $appKey = env('DROPBOX_APP_KEY');
            $appSecret = env('DROPBOX_APP_SECRET');
            $accessToken = $config['authorization_token']; // The current access token
            $refreshToken = env('DROPBOX_REFRESH_TOKEN'); // Your refresh token

            $app = new DropboxApp($appKey, $appSecret);

            // Create a Guzzle HTTP client
            $httpClient = new GuzzleClient();

            try {
                // Manually refresh the access token using the Dropbox API
                $response = $httpClient->post('https://api.dropboxapi.com/oauth2/token', [
                    'form_params' => [
                        'grant_type' => 'refresh_token',
                        'refresh_token' => $refreshToken,
                        'client_id' => $appKey,
                        'client_secret' => $appSecret,
                    ],
                ]);

                // Parse the response to get the refreshed access token
                $data = json_decode($response->getBody(), true);
                $refreshedAccessToken = $data['access_token'];

                // Update the access token in your configuration
                $config['access_token'] = $refreshedAccessToken;
            } catch (Exception $e) {
                // Handle any exceptions if the token refresh fails
                // You may need to re-authenticate the user or take appropriate action
            }

            // Initialize the Dropbox adapter with the updated access token
            $adapter = new DropboxAdapter(new DropboxClient($config['access_token']));

            // Create a new FilesystemAdapter with the Dropbox adapter
            return new FilesystemAdapter(
                new Filesystem($adapter, $config),
                $adapter,
                $config
            );
        });
    }
}
