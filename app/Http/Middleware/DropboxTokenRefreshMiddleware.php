<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Storage;
use Spatie\Dropbox\Client as DropboxClient;

class DropboxTokenRefreshMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $accessToken = env('DROPBOX_ACCESS_TOKEN');
        $apiEndpoint = 'https://api.dropbox.com/2/files/get_metadata'; // Replace with your Dropbox API endpoint

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
        ])->get($apiEndpoint);

        if ($response->status() === 401) {
            // Access token has expired; refresh it
            $this->refreshToken();

            // Retry the Dropbox request with the new access token
            $accessToken = env('DROPBOX_ACCESS_TOKEN'); // Get the updated access token
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get($apiEndpoint);
        }

        // Continue with the request or handle the response as needed
        return $next($request);
    }

    private function refreshToken()
    {
        $clientId = env('DROPBOX_CLIENT_ID');
        $clientSecret = env('DROPBOX_CLIENT_SECRET');
        $refreshToken = env('DROPBOX_REFRESH_TOKEN');

        $response = Http::asForm()->post('https://api.dropbox.com/oauth2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ])->withBasicAuth($clientId, $clientSecret);

        $result = $response->json();

        if ($response->successful() && isset($result['access_token'])) {
            // Update the access token in your .env file
            $envFilePath = base_path('.env');
            if (File::isWritable($envFilePath)) {
                File::put($envFilePath, str_replace(
                    "DROPBOX_ACCESS_TOKEN=" . env('DROPBOX_ACCESS_TOKEN'),
                    "DROPBOX_ACCESS_TOKEN={$result['access_token']}",
                    File::get($envFilePath)
                ));
            }
        }
    }
}
