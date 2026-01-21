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
     * Translate multiple texts at once using a single API call
     * This is much faster than translating one by one
     */
    public function translateBatch(array $texts, string $targetLang = 'ar', string $sourceLang = 'en'): array
    {
        if (empty($texts)) {
            return [];
        }

        $results = [];
        $textsToTranslate = [];
        $indexMap = [];

        // Check cache first for each text
        foreach ($texts as $key => $text) {
            $cacheKey = 'translate_' . md5($text . $targetLang . $sourceLang);
            $cached = Cache::get($cacheKey);
            if ($cached) {
                $results[$key] = $cached;
            } else {
                $textsToTranslate[$key] = $text;
                $indexMap[] = $key;
            }
        }

        // If all texts were cached, return results
        if (empty($textsToTranslate)) {
            return $results;
        }

        try {
            // Join all texts with a unique separator for batch translation
            $separator = ' ||| ';
            $combinedText = implode($separator, array_values($textsToTranslate));

            // Use Google Translate free API
            $url = 'https://translate.googleapis.com/translate_a/single';

            $response = Http::timeout(15)->get($url, [
                'client' => 'gtx',
                'sl' => $sourceLang,
                'tl' => $targetLang,
                'dt' => 't',
                'q' => $combinedText,
            ]);

            if ($response->successful()) {
                $result = $response->json();

                // Extract translated text from response
                $translatedText = '';
                if (isset($result[0]) && is_array($result[0])) {
                    foreach ($result[0] as $segment) {
                        if (isset($segment[0])) {
                            $translatedText .= $segment[0];
                        }
                    }
                }

                // Split back into individual translations
                $translatedParts = explode($separator, $translatedText);

                // Map translations back to original keys
                $i = 0;
                foreach ($textsToTranslate as $key => $originalText) {
                    $translated = isset($translatedParts[$i]) ? trim($translatedParts[$i]) : $originalText;
                    $results[$key] = $translated;

                    // Cache each translation for 24 hours
                    $cacheKey = 'translate_' . md5($originalText . $targetLang . $sourceLang);
                    Cache::put($cacheKey, $translated, 86400);
                    $i++;
                }
            } else {
                // If API fails, return original texts
                foreach ($textsToTranslate as $key => $text) {
                    $results[$key] = $text;
                }
            }
        } catch (\Exception $e) {
            Log::warning('Batch translation failed', [
                'count' => count($textsToTranslate),
                'error' => $e->getMessage()
            ]);
            // Return original texts on failure
            foreach ($textsToTranslate as $key => $text) {
                $results[$key] = $text;
            }
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
