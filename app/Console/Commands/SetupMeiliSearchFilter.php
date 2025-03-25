<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Meilisearch\Client;

class SetupMeiliSearchFilter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:setup-filter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup filterable attributes in Meilisearch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

        $index = $client->index('products'); // Replace 'products' with your actual index name
        $index->updateSettings([
            'filterableAttributes' => ['keyword', 'status'],
        ]);

        $this->info('Meilisearch filterable attributes setup successfully!');
    }
}