<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Meilisearch\Client;

class MeilisearchSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

        $index = $client->index('products'); // Replace 'products' with your actual index name
        $index->updateSettings([
            'filterableAttributes' => ['keyword', 'status'],
        ]);
    }
}