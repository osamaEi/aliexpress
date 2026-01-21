<?php

namespace App\Jobs;

use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslateProductTitlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The product titles to translate
     */
    protected array $titles;

    /**
     * Cache key prefix for storing results
     */
    protected string $cacheKeyPrefix;

    /**
     * Create a new job instance.
     */
    public function __construct(array $titles, string $cacheKeyPrefix = 'translation_')
    {
        $this->titles = $titles;
        $this->cacheKeyPrefix = $cacheKeyPrefix;
    }

    /**
     * Execute the job.
     */
    public function handle(TranslationService $translationService): void
    {
        if (empty($this->titles)) {
            return;
        }

        Log::info('TranslateProductTitlesJob started', [
            'count' => count($this->titles)
        ]);

        try {
            // Translate all titles in batch
            $translations = $translationService->translateBatch($this->titles, 'ar', 'en');

            // Cache each translation for 7 days
            foreach ($translations as $key => $translatedTitle) {
                $originalTitle = $this->titles[$key];
                $cacheKey = $this->cacheKeyPrefix . md5($originalTitle);
                Cache::put($cacheKey, $translatedTitle, 604800); // 7 days
            }

            Log::info('TranslateProductTitlesJob completed', [
                'translated' => count($translations)
            ]);
        } catch (\Exception $e) {
            Log::error('TranslateProductTitlesJob failed', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
