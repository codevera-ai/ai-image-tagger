<?php

namespace AIImageTagger\API;

use AIImageTagger\Services\EncryptionService;
use AIImageTagger\Storage\SettingsRepository;

abstract class AbstractProvider implements ProviderInterface {

    protected SettingsRepository $settings;
    protected EncryptionService $encryption;
    protected string $apiKey;

    public function __construct(
        SettingsRepository $settings,
        EncryptionService $encryption
    ) {
        $this->settings = $settings;
        $this->encryption = $encryption;
        $this->apiKey = $this->getApiKey();
    }

    abstract protected function getApiKey(): string;

    abstract protected function buildRequest(string $imagePath): array;

    abstract protected function parseResponse(string $response): array;

    protected function encodeImage(string $imagePath): string {
        $imageData = file_get_contents($imagePath);
        return base64_encode($imageData);
    }

    protected function getMimeType(string $imagePath): string {
        return mime_content_type($imagePath);
    }

    /**
     * Get the language name from WordPress locale
     */
    protected function getLanguageName(): string {
        $locale = get_locale();

        // Map common WordPress locales to language names
        $localeMap = [
            'en_US' => 'English',
            'en_GB' => 'English',
            'de_DE' => 'German',
            'de_CH' => 'German',
            'de_AT' => 'German',
            'fr_FR' => 'French',
            'fr_BE' => 'French',
            'fr_CA' => 'French',
            'es_ES' => 'Spanish',
            'es_MX' => 'Spanish',
            'es_AR' => 'Spanish',
            'it_IT' => 'Italian',
            'pt_PT' => 'Portuguese',
            'pt_BR' => 'Portuguese',
            'nl_NL' => 'Dutch',
            'nl_BE' => 'Dutch',
            'pl_PL' => 'Polish',
            'ru_RU' => 'Russian',
            'ja' => 'Japanese',
            'zh_CN' => 'Chinese',
            'zh_TW' => 'Chinese',
            'ko_KR' => 'Korean',
            'ar' => 'Arabic',
            'sv_SE' => 'Swedish',
            'da_DK' => 'Danish',
            'fi' => 'Finnish',
            'no_NO' => 'Norwegian',
            'tr_TR' => 'Turkish',
            'cs_CZ' => 'Czech',
            'el' => 'Greek',
            'hu_HU' => 'Hungarian',
            'ro_RO' => 'Romanian',
            'sk_SK' => 'Slovak',
            'uk' => 'Ukrainian',
            'he_IL' => 'Hebrew',
            'th' => 'Thai',
            'vi' => 'Vietnamese'
        ];

        if (isset($localeMap[$locale])) {
            return $localeMap[$locale];
        }

        // Fallback: try to extract language from locale code (e.g., 'de_DE' -> 'de')
        $languageCode = strtolower(substr($locale, 0, 2));

        // Additional fallback mappings for language codes
        $languageCodeMap = [
            'en' => 'English',
            'de' => 'German',
            'fr' => 'French',
            'es' => 'Spanish',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'nl' => 'Dutch',
            'pl' => 'Polish',
            'ru' => 'Russian',
            'ja' => 'Japanese',
            'zh' => 'Chinese',
            'ko' => 'Korean',
            'ar' => 'Arabic',
            'sv' => 'Swedish',
            'da' => 'Danish',
            'fi' => 'Finnish',
            'no' => 'Norwegian',
            'tr' => 'Turkish',
            'cs' => 'Czech',
            'el' => 'Greek',
            'hu' => 'Hungarian',
            'ro' => 'Romanian',
            'sk' => 'Slovak',
            'uk' => 'Ukrainian',
            'he' => 'Hebrew',
            'th' => 'Thai',
            'vi' => 'Vietnamese'
        ];

        if (isset($languageCodeMap[$languageCode])) {
            return $languageCodeMap[$languageCode];
        }

        // Default to English if unknown
        return 'English';
    }

    /**
     * Get language instruction for prompts
     */
    protected function getLanguageInstruction(): string {
        $language = $this->getLanguageName();

        if ($language === 'English') {
            return ''; // No need to specify for English
        }

        return "IMPORTANT: Provide all text fields (title, description, alt_text, caption, tags) in {$language}. ";
    }

    protected function optimizeImage(string $imagePath): string {
        // Resize if needed, return path to optimized image
        $maxDimension = 2048;

        $imageInfo = @getimagesize($imagePath);
        
        if (!$imageInfo) {
            return $imagePath;
        }
        
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        if ($width <= $maxDimension && $height <= $maxDimension) {
            return $imagePath;
        }

        // For now, just return the original
        // TODO: Implement actual image resizing
        return $imagePath;
    }

    protected function makeRequest(string $url, array $data, array $headers, int $timeout = 60): string {
        // Convert headers array to associative array for wp_remote_post
        $headersAssoc = [];
        foreach ($headers as $header) {
            $parts = explode(':', $header, 2);
            if (count($parts) === 2) {
                $headersAssoc[trim($parts[0])] = trim($parts[1]);
            }
        }

        $args = [
            'body' => wp_json_encode($data),
            'headers' => $headersAssoc,
            'timeout' => $timeout,
            'method' => 'POST',
            'data_format' => 'body',
        ];

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            throw new \Exception("Connection error: " . esc_html($response->get_error_message()));
        }

        $httpCode = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($httpCode !== 200) {
            // Try to parse error response as JSON to extract meaningful error message
            $errorMessage = $this->parseErrorResponse($body, $httpCode);
            throw new \Exception(esc_html($errorMessage));
        }

        return $body;
    }

    /**
     * Parse error response to extract meaningful error message
     */
    protected function parseErrorResponse(string $body, int $httpCode): string {
        // Try to decode as JSON
        $decoded = json_decode($body, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            // Try common error message paths
            if (isset($decoded['error']['message'])) {
                return "API error (HTTP {$httpCode}): " . $decoded['error']['message'];
            }

            if (isset($decoded['error']) && is_string($decoded['error'])) {
                return "API error (HTTP {$httpCode}): " . $decoded['error'];
            }

            if (isset($decoded['message'])) {
                return "API error (HTTP {$httpCode}): " . $decoded['message'];
            }

            // If we have error type information, include it
            if (isset($decoded['error']['type'])) {
                $type = $decoded['error']['type'];
                return "API error (HTTP {$httpCode}): {$type}";
            }
        }

        // If not JSON or no recognisable error structure, return the raw body (truncated if too long)
        if (strlen($body) > 200) {
            return "HTTP {$httpCode}: " . substr($body, 0, 200) . '...';
        }

        return "HTTP {$httpCode}: {$body}";
    }
}
