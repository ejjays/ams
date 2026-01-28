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
    public static function ask($prompt, $isJson = false) {
        self::init();
        if (!self::$apiKey) return "AI Error: API Key not found";

        $model = 'gemini-2.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . self::$apiKey;
        
        $data = [
            "contents" => [["parts" => [["text" => $prompt]]]],
            "generationConfig" => [
                "temperature" => 0.2,
                "maxOutputTokens" => 2000,
                "responseMimeType" => $isJson ? "application/json" : "text/plain"
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        // --- BYPASS SSL FOR LOCAL SERVERS ---
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $err = curl_error($ch);
            error_log("Gemini Connection Error: " . $err);
            return "Connection Error: " . $err;
        }

        if ($response) {
            $json = json_decode($response, true);
            
            if (isset($json['error'])) {
                $msg = $json['error']['message'] ?? 'Unknown API Error';
                error_log("Gemini API Error: " . $msg);
                return "API Error: " . $msg;
            }

            $resText = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($resText) return $resText;
            
            return "AI Error: Response received but content is missing. Raw: " . substr($response, 0, 100);
        }
        return "AI Error: Server returned no response (Check network/CURL).";
    }

    /**
     * AI Progress Analytics: Returns JSON
     */
    public static function getProgressSummary($stats) {
        $prompt = "You are a Senior Accreditation Consultant. Analyze this compliance data: ";
        foreach($stats as $s) {
            $prompt .= "{$s['program']} ({$s['percentage']}%), ";
        }
        $prompt .= ". Respond ONLY with a JSON object: {\"summary\": \"2 sentences\", \"action\": \"1 recommendation\"}. No other text.";
        
        $response = self::ask($prompt, true);
        
        // --- ULTRA ROBUST JSON CLEANER ---
        if ($response) {
            // Find the first '{' and last '}'
            $start = strpos($response, '{');
            $end = strrpos($response, '}');
            if ($start !== false && $end !== false) {
                $response = substr($response, $start, $end - $start + 1);
            }
        }
        
        return $response ?: json_encode([
            "summary" => "Institutional compliance metrics are being analyzed.",
            "action" => "Review program indicators for improvement."
        ]);
    }

    /**
     * AI Document Insight: Analyzes a document's relevance
     */
    public static function getDocumentInsight($title, $comment, $content = null) {
        if (!$title) return "Document title is missing. Please add a title to get AI insights.";
        
        $prompt = "You are an Accreditation Expert. Analyze this document's importance for university accreditation.\n\n";
        $prompt .= "TITLE: {$title}\n";
        $prompt .= "DESCRIPTION: {$comment}\n";
        
        if ($content) {
            $prompt .= "CONTENT PREVIEW: " . substr($content, 0, 2000) . "\n\n";
        }
        
        $prompt .= "Explain in 2 concise sentences what specific accreditation 'Area' or 'Standard' this document likely supports based on its metadata and content.";
        
        return self::ask($prompt) ?: "AI is currently unable to analyze this specific document. Please ensure the title is descriptive.";
    }

    /**
     * AI Auto-Tagging: Picks the best indicator for a document
     */
    public static function suggestIndicator($docTitle, $indicators) {
        $indList = "";
        foreach($indicators as $ind) { $indList .= "[ID:{$ind['id']}] {$ind['title']}\n"; }
        
        $prompt = "Given the document title '{$docTitle}', pick the most relevant Indicator ID from this list. Return ONLY the ID number, nothing else:\n" . $indList;
        $result = trim((string)self::ask($prompt));
        return is_numeric($result) ? (int)$result : null;
    }
}