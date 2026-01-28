<?php
/**
 * PHP/services/AIService.php
 * Hybrid AI Service: Gemini 2.5 Flash (Vision) + Llama 3.3 (Groq).
 * Optimized for 2026 Rate Limits.
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
     * @param string $prompt
     * @param bool $isJson
     * @param string $forceModel 'gemini' | 'llama' | 'hybrid'
     * @param array $fileData ['mime' => string, 'base64' => string]
     */
    public static function ask($prompt, $isJson = false, $forceModel = 'hybrid', $fileData = null) {
        self::init();

        // 1. If forcing Llama or it's a simple text task
        if ($forceModel === 'llama') {
            return ['text' => self::callGroq($prompt, $isJson), 'model' => 'Meta Llama 3.3 (Groq)'];
        }

        // 2. If forcing Gemini or Multimodal (Vision)
        if ($forceModel === 'gemini' || $fileData) {
            $res = self::callGemini($prompt, $isJson, $fileData);
            
            // IF VISION FAILS, AUTO-FALLBACK TO LLAMA (METADATA ONLY)
            if (!$res || strpos($res, 'AI Error:') === 0) {
                error_log("🤖 AI: Vision failed. Falling back to Llama Metadata Analysis...");
                $res = self::callGroq($prompt, $isJson);
                return ['text' => $res, 'model' => 'Meta Llama 3.3 (Metadata Fallback)'];
            }
            
            return ['text' => $res, 'model' => 'Google Gemini 2.5 Flash (Vision)'];
        }

        // 3. Default Hybrid Fallback
        $res = self::callGemini($prompt, $isJson);
        if ($res && strpos($res, 'AI Error:') !== 0) {
            return ['text' => $res, 'model' => 'Google Gemini 2.5 Flash'];
        }

        return ['text' => self::callGroq($prompt, $isJson), 'model' => 'Meta Llama 3.3 (Groq)'];
    }

    private static function callGemini($prompt, $isJson, $fileData = null) {
        if (!self::$geminiKey) return "AI Error: Gemini Key missing.";
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . self::$geminiKey;
        
        $parts = [["text" => $prompt]];
        if ($fileData) {
            $parts[] = [
                "inline_data" => [
                    "mime_type" => $fileData['mime'],
                    "data" => $fileData['base64']
                ]
            ];
        }

        $data = [
            "contents" => [["parts" => $parts]],
            "generationConfig" => [
                "temperature" => 0.2,
                "responseMimeType" => $isJson ? "application/json" : "text/plain"
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $json = json_decode($response, true);
        return $json['candidates'][0]['content']['parts'][0]['text'] ?? "AI Error: Gemini failed.";
    }

    private static function callGroq($prompt, $isJson) {
        if (!self::$groqKey) return "AI Error: Groq Key missing.";
        $url = "https://api.groq.com/openai/v1/chat/completions";
        $data = [
            "model" => "llama-3.3-70b-versatile",
            "messages" => [["role" => "user", "content" => $prompt]],
            "temperature" => 0.2
        ];
        if ($isJson) $data["response_format"] = ["type" => "json_object"];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . self::$groqKey]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $json = json_decode($response, true);
        return $json['choices'][0]['message']['content'] ?? "AI Error: Llama failed.";
    }

    public static function getProgressSummary($stats) {
        $prompt = "Accreditation Analysis: ";
        foreach($stats as $s) { $prompt .= "{$s['program']} ({$s['percentage']}%), "; }
        $prompt .= ". JSON respond: {\"summary\": \"2 sentences\", \"action\": \"1 recommendation\"}.";
        
        // DASHBOARD USES LLAMA ONLY TO SAVE QUOTA
        $result = self::ask($prompt, true, 'llama'); 
        $response = $result['text'];
        
        if ($response) {
            $start = strpos($response, '{');
            $end = strrpos($response, '}');
            if ($start !== false && $end !== false) $response = substr($response, $start, $end - $start + 1);
        }
        return ['summary' => $response, 'model' => $result['model']];
    }

    public static function getDocumentInsight($title, $comment, $fileInfo = null) {
        $prompt = "Accreditation Analysis for '{$title}'. ";
        if ($comment) $prompt .= "Context: {$comment}. ";
        $prompt .= "Explain importance in 2 sentences. Direct response only.";

        // SMART ROUTING
        if ($fileInfo && in_array($fileInfo['ext'], ['pdf', 'jpg', 'jpeg', 'png'])) {
            // USE GEMINI VISION FOR COMPLEX FILES
            return self::ask($prompt, false, 'gemini', $fileInfo);
        } else {
            // USE LLAMA FOR TEXT/METADATA TO SAVE QUOTA
            if ($fileInfo && isset($fileInfo['content'])) $prompt .= "\nContent: " . substr($fileInfo['content'], 0, 1000);
            return self::ask($prompt, false, 'llama');
        }
    }

    public static function suggestIndicator($docTitle, $indicators) {
        if (empty($indicators)) return null;
        $indList = "";
        foreach($indicators as $ind) { $indList .= "[ID:{$ind['id']}] {$ind['title']}\n"; }
        $prompt = "Match '{$docTitle}' to a Category ID from this list. Respond with ONLY the ID number:\n" . $indList;
        
        // AUTO-TAGGING USES LLAMA ONLY TO SAVE QUOTA
        $result = self::ask($prompt, false, 'llama');
        if (preg_match('/(\d+)/', $result['text'], $m)) return (int)$m[1];
        return null;
    }
}