<?php
/**
 * PHP/services/AIService.php
 * Hybrid AI Service: Gemini 2.5 Flash with Llama 4 (Groq) Fallback.
 */

class AIService {
    private static $geminiKey = null;
    private static $groqKey = null;

    private static function init() {
        if (self::$geminiKey !== null) return;

        $rootEnv = dirname(dirname(__DIR__)) . '/.env';
        if (file_exists($rootEnv)) {
            $lines = file($rootEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $val = trim($parts[1]);
                    if ($key === 'GEMINI_API_KEY') self::$geminiKey = $val;
                    if ($key === 'GROQ_API_KEY') self::$groqKey = $val;
                }
            }
        }
    }

    /**
     * The Master Ask Method with Fallback
     * Returns ['text' => string, 'model' => string]
     */
    public static function ask($prompt, $isJson = false) {
        self::init();

        // 1. TRY GEMINI FIRST
        $res = self::callGemini($prompt, $isJson);
        if ($res && strpos($res, 'AI Error:') !== 0 && strpos($res, 'API Error:') !== 0) {
            return ['text' => $res, 'model' => 'Google Gemini 2.5 Flash'];
        }
        
        // 2. IF GEMINI FAILS, FALLBACK TO GROQ
        error_log("🤖 AI: Gemini failed or limited. Falling back to Llama 4 (Groq)...");
        $res = self::callGroq($prompt, $isJson);
        
        return ['text' => $res, 'model' => 'Meta Llama 4 (Groq)'];
    }

    private static function callGemini($prompt, $isJson) {
        if (!self::$geminiKey) return null;
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . self::$geminiKey;
        $data = [
            "contents" => [["parts" => [["text" => $prompt]]]],
            "generationConfig" => [
                "temperature" => 0.2,
                "maxOutputTokens" => 1000,
                "responseMimeType" => $isJson ? "application/json" : "text/plain"
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $err = curl_errno($ch);

        if ($err || !$response) return null;
        $json = json_decode($response, true);
        return $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    private static function callGroq($prompt, $isJson) {
        if (!self::$groqKey) return "AI Error: No backup API key configured.";
        
        $url = "https://api.groq.com/openai/v1/chat/completions";
        $data = [
            "model" => "meta-llama/llama-4-scout-17b-16e-instruct",
            "messages" => [["role" => "user", "content" => $prompt]],
            "temperature" => 0.2
        ];
        
        if ($isJson) {
            $data["response_format"] = ["type" => "json_object"];
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . self::$groqKey
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $curlErr = curl_error($ch);

        if ($curlErr) {
            error_log("Groq CURL Error: " . $curlErr);
            return "AI Error: Groq Connection Failed";
        }

        if (!$response) return "AI Error: Groq returned no response.";
        
        $json = json_decode($response, true);
        return $json['choices'][0]['message']['content'] ?? "AI Error: Llama 4 failed.";
    }

    // --- SHARED LOGIC METHODS ---

    public static function getProgressSummary($stats) {
        $prompt = "You are a Senior Accreditation Consultant. Analyze this compliance data: ";
        foreach($stats as $s) {
            $prompt .= "{$s['program']} ({$s['percentage']}%), ";
        }
        $prompt .= ". Respond ONLY with a JSON object: {\"summary\": \"2 sentences\", \"action\": \"1 recommendation\"}. No other text.";
        
        $result = self::ask($prompt, true);
        $response = $result['text'];
        
        // --- ULTRA ROBUST JSON CLEANER ---
        if ($response) {
            $start = strpos($response, '{');
            $end = strrpos($response, '}');
            if ($start !== false && $end !== false) $response = substr($response, $start, $end - $start + 1);
        }
        
        return [
            'summary' => $response ?: json_encode(["summary" => "Metrics analyzed.", "action" => "Review indicators."]),
            'model' => $result['model']
        ];
    }

    public static function getDocumentInsight($title, $comment, $content = null) {
        if (!$title) return ["insight" => "Title missing.", "model" => "None"];
        $prompt = "Accreditation Expert Analysis:\nTITLE: {$title}\nDESC: {$comment}\n";
        if ($content) $prompt .= "CONTENT: " . substr($content, 0, 1500);
        $prompt .= "\nExplain importance in 2 concise sentences.";
        
        $result = self::ask($prompt);
        return [
            "insight" => $result['text'] ?: "AI is currently unable to analyze this document.",
            "model" => $result['model']
        ];
    }

    public static function suggestIndicator($docTitle, $indicators) {
        $indList = "";
        foreach($indicators as $ind) { $indList .= "[ID:{$ind['id']}] {$ind['title']}\n"; }
        $prompt = "Match document '{$docTitle}' to an Indicator ID from this list. Return ONLY the ID number:\n" . $indList;
        
        $result = self::ask($prompt);
        $text = trim((string)$result['text']);
        
        return is_numeric($text) ? (int)$text : null;
    }
}
