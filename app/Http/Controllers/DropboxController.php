<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class DropboxController extends Controller
{
    public function makeDropboxRequest()
    {
        $accessToken = env('DROPBOX_ACCESS_TOKEN');
        $apiEndpoint = 'https://api.dropbox.com/2/files/get_metadata'; // Replace with your Dropbox API endpoint

        $client = new Client();
        $response = $client->get($apiEndpoint, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);

        if ($response->getStatusCode() == 401) {
            // Access token has expired; refresh it
            $this->refreshToken();

            // Retry the Dropbox request with the new access token
            $accessToken = env('DROPBOX_ACCESS_TOKEN'); // Get the updated access token
            $response = $client->get($apiEndpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
            ]);
        }

        // Handle the Dropbox API response
        $data = json_decode($response->getBody(), true);

        // Your code to process the API response
    }


    public function refreshToken()
    {
        $client = new Client();

        $response = $client->post('https://api.dropbox.com/oauth2/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => env('DROPBOX_REFRESH_TOKEN'), // Get the refresh token from your .env file
                'client_id' => env('DROPBOX_APP_KEY'),           // Get the app key from your .env file
                'client_secret' => env('DROPBOX_APP_SECRET'),   // Get the app secret from your .env file
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        // Update your .env file with the new access token
        if (isset($data['access_token'])) {
            $newAccessToken = $data['access_token'];
            // Use the "Dotenv" package to update the .env file
            \Dotenv\Dotenv::createImmutable(base_path())->load();
            \Dotenv\Dotenv::createImmutable(base_path())->setKey('DROPBOX_AUTH_TOKEN', $newAccessToken)->save();
        } else {
            // Handle the error response
            // You can log or return an error response
        }
    }
}
