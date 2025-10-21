<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AliExpressAuth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'aliexpress:auth {action=url : Action to perform (url, token)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AliExpress OAuth2 authorization URL or exchange code for token';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        if ($action === 'url') {
            $this->generateAuthUrl();
        } elseif ($action === 'token') {
            $this->exchangeCodeForToken();
        } else {
            $this->error('Invalid action. Use: url or token');
            return 1;
        }

        return 0;
    }

    /**
     * Generate OAuth2 authorization URL
     */
    protected function generateAuthUrl()
    {
        $apiKey = config('services.aliexpress.api_key');
        $redirectUri = $this->ask('Enter your redirect URI (or press enter for default)', 'http://localhost:8000/aliexpress/callback');
        $state = bin2hex(random_bytes(16));

        $authUrl = "https://api-sg.aliexpress.com/oauth/authorize?" . http_build_query([
            'response_type' => 'code',
            'client_id' => $apiKey,
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);

        $this->info('=================================================');
        $this->info('AliExpress OAuth2 Authorization');
        $this->info('=================================================');
        $this->newLine();
        $this->line('Step 1: Open this URL in your browser:');
        $this->newLine();
        $this->line($authUrl);
        $this->newLine();
        $this->line('Step 2: Login and authorize the application');
        $this->line('Step 3: You will be redirected to your callback URL with a "code" parameter');
        $this->line('Step 4: Copy the code and run:');
        $this->info('   php artisan aliexpress:auth token');
        $this->newLine();
        $this->warn('State (save this): ' . $state);
        $this->newLine();
    }

    /**
     * Exchange authorization code for access token
     */
    protected function exchangeCodeForToken()
    {
        $apiKey = config('services.aliexpress.api_key');
        $apiSecret = config('services.aliexpress.api_secret');

        $this->info('=================================================');
        $this->info('Exchange Authorization Code for Access Token');
        $this->info('=================================================');
        $this->newLine();

        $code = $this->ask('Enter the authorization code from the callback URL');
        $redirectUri = $this->ask('Enter the same redirect URI you used before', 'http://localhost:8000/aliexpress/callback');

        if (!$code) {
            $this->error('Authorization code is required!');
            return;
        }

        $this->info('Requesting access token...');

        try {
            $response = Http::asForm()->post('https://api-sg.aliexpress.com/oauth/token', [
                'client_id' => $apiKey,
                'client_secret' => $apiSecret,
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $this->newLine();
                $this->info('✓ Success! Access token obtained.');
                $this->newLine();
                $this->line('Access Token: ' . ($data['access_token'] ?? 'N/A'));
                $this->line('Refresh Token: ' . ($data['refresh_token'] ?? 'N/A'));
                $this->line('Expires In: ' . ($data['expires_in'] ?? 'N/A') . ' seconds');
                $this->line('Refresh Token Valid: ' . ($data['refresh_token_valid_time'] ?? 'N/A') . ' seconds');
                $this->newLine();
                $this->info('Add this to your .env file:');
                $this->line('ALIEXPRESS_ACCESS_TOKEN=' . ($data['access_token'] ?? ''));
                $this->newLine();

                if ($this->confirm('Would you like to save this to .env automatically?', true)) {
                    $this->updateEnvFile('ALIEXPRESS_ACCESS_TOKEN', $data['access_token'] ?? '');
                    $this->info('✓ .env file updated!');
                }
            } else {
                $this->error('✗ Failed to get access token');
                $this->line('Response: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Update .env file
     */
    protected function updateEnvFile($key, $value)
    {
        $envPath = base_path('.env');
        $envContent = file_get_contents($envPath);

        if (str_contains($envContent, $key)) {
            // Update existing key
            $envContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $envContent
            );
        } else {
            // Add new key
            $envContent .= "\n{$key}={$value}\n";
        }

        file_put_contents($envPath, $envContent);
    }
}
