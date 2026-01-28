<?php
/**
 * PHP/services/Gemini.php
 * A centralized service for interacting with Gemini 2.5 Flash.
 */

class Gemini {
    private static $apiKey = null;

    private static function init() {
        if (self::$apiKey !== null) return;

        // --- Look for .env in the Project Root ---
        $rootEnv = dirname(dirname(__DIR__)) . '/.env';
        if (file_exists($rootEnv)) {
            $lines = file($rootEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $val = trim($parts[1]);
                    if (!getenv($key)) putenv("$key=$val");
                    if ($key === 'GEMINI_API_KEY') self::$apiKey = $val;
                }
            }
        }

        if (!self::$apiKey) self::$apiKey = getenv('GEMINI_API_KEY');
    }

    /**
     * Generic wrapper for Gemini API
     */
    public static function ask($prompt) {
        self::init();
        if (!self::$apiKey) return null;

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . self::$apiKey;
        $data = [
            "contents" => [["parts" => [["text" => $prompt]]]],
            "generationConfig" => ["temperature" => 0.7, "maxOutputTokens" => 800]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($response, true);
        return $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    /**
     * AI Progress Analytics: Generates a summary for the dashboard
     */
    public static function getProgressSummary($stats) {
        $prompt = "As an Accreditation Assistant, summarize this progress for the dashboard in 2-3 concise sentences. Mention specific percentages: ";
        foreach($stats as $s) {
            $prompt .= "{$s['program']}: {$s['percentage']}%, ";
        }
        return self::ask($prompt) ?: "Progress data updated. Please check compliance details per program.";
    }

    /**
     * AI Auto-Tagging: Picks the best indicator for a document
     */
    public static function suggestIndicator($docTitle, $indicators) {
        $indList = "";
        foreach($indicators as $ind) { $indList .= "[ID:{$ind['id']}] {$ind['title']}\n"; }
        
        $prompt = "Given the document title '{$docTitle}', pick the most relevant Indicator ID from this list. Return ONLY the ID number, nothing else:\n" . $indList;
        $result = trim(self::ask($prompt));
        return is_numeric($result) ? (int)$result : null;
    }
}