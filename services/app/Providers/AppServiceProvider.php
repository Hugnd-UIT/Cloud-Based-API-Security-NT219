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
        $vaultUrl = env('VAULT_URL', 'http://payshield_vault:8200') . '/v1/secret/data/payshield';
        $token    = env('VAULT_APP_TOKEN');

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'X-Vault-Token' => $token
            ])->timeout(3)->get($vaultUrl);

            if ($response->successful()) {
                $secrets = $response->json()['data']['data'];

                Config::set('app.key', $secrets['APP_KEY']);
                Config::set('database.connections.mysql.username', $secrets['DB_USERNAME']);
                Config::set('database.connections.mysql.password', $secrets['DB_PASSWORD']);
                Config::set('database.connections.mysql.database', $secrets['DB_DATABASE']);
                Config::set('services.keycloak.admin_user', $secrets['KC_ADMIN_USER'] ?? null);
                Config::set('services.keycloak.admin_password', $secrets['KC_ADMIN_PASSWORD'] ?? null);
                Config::set('services.payshield.client_secret', $secrets['CLIENT_SECRET'] ?? null);
                Config::set('services.payshield.webhook_secret', $secrets['WEBHOOK_SECRET'] ?? null);
                DB::purge('mysql');
            }
        } catch (\Exception $e) {
            Log::error('Cannot connect to Vault: ' . $e->getMessage());
        }
    }
}