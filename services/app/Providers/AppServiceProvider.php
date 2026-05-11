<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        $vaultUrl = env('VAULT_URL', 'https://payshield_vault:8200') . '/v1/secret/data/payshield';
        $token    = env('VAULT_APP_TOKEN');

        if ($token) {
            try {
                /** @var \Illuminate\Http\Client\Response $response */
                $response = Http::withOptions([
                    'cert'    => [base_path('cert/client.crt')], 
                    'ssl_key' => [base_path('key/client.key')],
                    'verify'  => base_path('cert/ca.crt'),
                ])->withHeaders([
                    'X-Vault-Token' => $token
                ])->timeout(5)->get($vaultUrl);

                if ($response->successful()) {
                    $secrets = $response->json()['data']['data'];
                    Config::set('app.key', $secrets['APP_KEY'] ?? null);
                    Config::set('database.connections.mysql.username', $secrets['DB_USERNAME'] ?? null);
                    Config::set('database.connections.mysql.password', $secrets['DB_PASSWORD'] ?? null);
                    Config::set('database.connections.mysql.database', $secrets['DB_DATABASE'] ?? null);
                    Config::set('services.keycloak.admin_user', $secrets['KC_ADMIN_USER'] ?? null);
                    Config::set('services.keycloak.admin_password', $secrets['KC_ADMIN_PASSWORD'] ?? null);
                    Config::set('services.payshield.client_secret', $secrets['CLIENT_SECRET'] ?? null);
                    Config::set('services.payshield.webhook_secret', $secrets['WEBHOOK_SECRET'] ?? null);
                    DB::purge('mysql');
                } else {
                    Log::error('Vault mTLS Rejected - Status: ' . $response->status() . ' - ' . $response->body());
                }
            } catch (\Exception $e) {
                Log::error('Cannot connect to Vault using mTLS: ' . $e->getMessage());
            }
        }

        \Illuminate\Support\Facades\Http::macro('keycloak', function () {
            return \Illuminate\Support\Facades\Http::withOptions([
                'verify'  => '/kms/ca.crt',
                'cert'    => '/kms/client.crt', 
                'ssl_key' => '/kms/client.key',
                'curl'    => [
                    CURLOPT_SSL_VERIFYHOST => 0, 
                ]
            ])->baseUrl('https://keycloak:8443'); 
        });
    }
}