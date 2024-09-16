<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;

class PassportClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $redirectUri = env('REDIRECT_URI');
        $clientRepository = new ClientRepository();

        $client = $clientRepository->createPersonalAccessClient(
            null,
            'web', // İstemci adı
            $redirectUri // Yönlendirme URI'si
        );
        echo "Client ID: " . $client->id . "\n";
        echo "Client Secret: " . $client->secret . "\n";
    }
}
