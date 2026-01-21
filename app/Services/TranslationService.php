<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    /**
     * Translate text using Google Translate (free method)
     */
    public function translate(string $text, string $targetLang = 'ar', string $sourceLang = 'en'): string
    {
        if (empty($text)) {
            return $text;
        }

        // Check cache first
        $cacheKey = 'translate_' . md5($text . $targetLang . $sourceLang);
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }

        try {
            // Use Google Translate free API
            $url = 'https://translate.googleapis.com/translate_a/single';

            $response = Http::timeout(5)->get($url, [
                'client' => 'gtx',
                'sl' => $sourceLang,
                'tl' => $targetLang,
                'dt' => 't',
                'q' => $text,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if (isset($result[0][0][0])) {
                    $translated = $result[0][0][0];
                    // Cache for 24 hours
                    Cache::put($cacheKey, $translated, 86400);
                    return $translated;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Translation failed', [
                'text' => substr($text, 0, 100),
                'error' => $e->getMessage()
            ]);
        }

        return $text;
    }

    /**
     * Translate multiple texts at once
     */
    public function translateBatch(array $texts, string $targetLang = 'ar', string $sourceLang = 'en'): array
    {
        $results = [];
        foreach ($texts as $key => $text) {
            $results[$key] = $this->translate($text, $targetLang, $sourceLang);
        }
        return $results;
    }

    /**
     * Detect if text is Arabic
     */
    public function isArabic(string $text): bool
    {
        return preg_match('/[\x{0600}-\x{06FF}]/u', $text) > 0;
    }

    /**
     * Detect if text is English
     */
    public function isEnglish(string $text): bool
    {
        return preg_match('/^[a-zA-Z0-9\s\-\.,!?\'\"]+$/', $text) > 0;
    }
}
