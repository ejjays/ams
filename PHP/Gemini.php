<?php
/**
 * PHP/Gemini.php
 * A simple helper for interacting with Gemini API.
 */

class Gemini {
    private static $apiKey = null;

    private static function init() {
        if (self::$apiKey !== null) return;

        // Try to get from .env or environment
        if (file_exists(__DIR__ . '/.env')) {
            $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), 'GEMINI_API_KEY=') === 0) {
                    self::$apiKey = trim(substr($line, 15));
                }
            }
        }

        if (!self::$apiKey) {
            self::$apiKey = getenv('GEMINI_API_KEY');
        }
    }

    /**
     * Sends a prompt to Gemini 1.5 Flash
     * @param string $prompt
     * @return string|null
     */
    public static function ask($prompt) {
        self::init();
        if (!self::$apiKey) return "API Key not configured.";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . self::$apiKey;

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 800
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) return null;
        curl_close($ch);

        $json = json_decode($response, true);
        return $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }
}
