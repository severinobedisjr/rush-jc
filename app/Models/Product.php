<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Meilisearch\Client;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'research_title',
        'abstract',
        'keyword',
        'authors',
        'pdf_path',
        'status', // Add status to fillable to allow mass assignment
    ];

    protected $casts = [
        'authors' => 'array',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'research_title' => $this->research_title,
            'authors'        => $this->authors,
            'keyword'        => $this->keyword,
            'abstract'       => $this->abstract,
            'status'        => $this->status, // Add status to searchable array
        ];
    }

    protected static function booted()
    {
        static::saved(function ($product) {
            Log::info("Product saved.  Attempting to update Meilisearch settings for product ID: " . $product->id);
            Log::info("Meilisearch Host: " . config('scout.meilisearch.host'));
            Log::info("Meilisearch Key: " . config('scout.meilisearch.key'));

            try {
                // Initialize Meilisearch client
                $client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));

                // Update filterable attributes settings
                $index = $client->index($product->searchableAs()); // "products"

                // Get current settings with try-catch
                try {
                    $settings = $index->getSettings();
                } catch (\Exception $e) {
                    Log::error("Failed to retrieve Meilisearch settings: " . $e->getMessage());
                    return; // Stop processing if we can't get settings
                }

                // Check if 'status' is already in the filterableAttributes array
                $filterableAttributes = $settings->filterableAttributes ?? []; // Use null coalescing operator
                if (!in_array('status', $filterableAttributes)) {
                    $newFilterableAttributes = array_merge($filterableAttributes, ['status']);
                    try {
                        $index->updateSettings([
                            'filterableAttributes' => $newFilterableAttributes,
                        ]);
                        Log::info("Successfully updated filterable attributes to: " . implode(', ', $newFilterableAttributes));
                    } catch (\Exception $e) {
                        Log::error("Failed to update Meilisearch settings: " . $e->getMessage());
                    }
                } else {
                    Log::info("Status is already a filterable attribute.");
                }

            } catch (\Exception $e) {
                Log::error("Meilisearch update settings failed: " . $e->getMessage());
            }
        });
    }
}