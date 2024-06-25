<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Google\Exception;
use Google_Client;
use Google_Service_Drive;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GoogleDriveAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:google-drive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Google Drive OAuth token and save to .env file';

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle()
    {
        $jsonFilePath = base_path('google-oauth.json');
        if (!File::exists($jsonFilePath)) {
            $this->error('OAuth JSON file not found at: ' . $jsonFilePath);
            return 1;
        }

        // Initialize the Google client
        $client = new Google_Client();
        $client->setAuthConfig($jsonFilePath);
        $client->setRedirectUri(route('google.callback'));
        $client->addScope(Google_Service_Drive::DRIVE);
        $client->setAccessType('offline');


        // Generate the authorization URL
        $authUrl = $client->createAuthUrl();
        $this->info('Open the following link in your browser:');
        $this->info($authUrl);

        // Prompt user to enter the authorization code
        $authCode = $this->ask('Enter the authorization code:');

        // Exchange authorization code for an access token
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        if (array_key_exists('error', $accessToken)) {
            $this->error('Error obtaining access token: ' . implode(', ', $accessToken));
            return 1;
        }

        Setting::updateOrCreate(['key' => Setting::GOOGLE_DRIVE_ACCESS_TOKEN], ['value' => json_encode($accessToken)]);

        $this->info('Tokens saved in settings');
        return 0;
    }
}
