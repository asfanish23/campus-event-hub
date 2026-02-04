<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    
    /**
     * Menggunakan Gemini 2.5 Flash sebagai default, fallback ke Lite jika rate limit
     */
    protected $primaryModel = 'gemini-2.5-flash';
    protected $fallbackModel = 'gemini-2.5-flash-lite';
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
    protected $maxRetries = 3;
    protected $currentModel;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->currentModel = $this->primaryModel;
    }

    public function generateEventDescription($eventName, $category, $location, $attendees = null, $extraDetails = null)
    {
        $prompt = $this->buildPrompt($eventName, $category, $location, $attendees, $extraDetails);
        return $this->callGemini($prompt);
    }

    public function tweakDescription($currentText, $style)
    {
        $instructions = [
            'funnier' => 'Make this event description even funnier with more Malaysian student slang. Use words like "mantap", "gempak", or "onsz".',
            'professional' => 'Rewrite this in a professional and formal tone suitable for a university official announcement.',
            'shorter' => 'Summarize this into a very short and punchy social media caption (max 50 words).'
        ];

        $selectedInstruction = $instructions[$style] ?? $instructions['funnier'];
        $prompt = "Current Description: $currentText\n\nInstruction: $selectedInstruction\n\nEnsure the response is a complete paragraph.";

        return $this->callGemini($prompt);
    }

    private function callGemini($prompt)
    {
        if (!$this->apiKey) {
            return ['success' => false, 'error' => 'API Key is missing in .env'];
        }

        try {
            $response = null;

            for ($attempt = 1; $attempt <= $this->maxRetries; $attempt++) {
                if ($attempt > 1) {
                    $delay = pow(2, $attempt - 1);
                    sleep($delay);
                }

                $modelUrl = $this->baseUrl . '/' . $this->currentModel . ':generateContent';
                
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post($modelUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.9,      // Lebih kreatif untuk slang student
                        'maxOutputTokens' => 2000, // Nilai tinggi supaya ayat TIDAK tergantung
                        'topP' => 0.95,
                    ]
                ]);

                // Jika rate limited, tukar ke Lite model
                if ($response->status() === 429 && $this->currentModel === $this->primaryModel) {
                    Log::warning('Rate limit hit on ' . $this->primaryModel . ', switching to ' . $this->fallbackModel);
                    $this->currentModel = $this->fallbackModel;
                    continue;
                }

                if ($response->status() !== 429) break;
            }

            if ($response->failed()) {
                Log::error('Gemini API Error Detail', [
                    'status' => $response->status(),
                    'model' => $this->currentModel,
                    'response' => $response->json()
                ]);

                return [
                    'success' => false, 
                    'error' => $response->status() === 429 ? 'Rate limit hit. Cuba lagi kejap lagi.' : 'API Error: ' . $response->status()
                ];
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            return $text 
                ? ['success' => true, 'text' => trim($text), 'model' => $this->currentModel] 
                : ['success' => false, 'error' => 'AI returned empty response'];

        } catch (\Exception $e) {
            Log::error('Gemini Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Connection error: ' . $e->getMessage()];
        }
    }

    private function buildPrompt($eventName, $category, $location, $attendees, $extraDetails = null)
    {
        $prompt = "You are a pro campus event promoter for 'Campus Event Hub'. ";
        $prompt .= "Task: Write a complete, catchy, and energetic event description for: '$eventName'. ";
        $prompt .= "Context: Category is $category and it will be at $location. ";
        $prompt .= "Tone: Mix of English and casual Malaysian student slang (Manglish). Make it sound very 'padu' and exciting. ";
        $prompt .= "Requirement: Use emojis, include a clear 'Call to Action' at the end, and DO NOT stop mid-sentence.";
        
        if ($attendees) {
            $prompt .= $attendees > 100 
                ? " Highlight that this is a massive event you don't want to miss!" 
                : " Highlight that slots are very limited and exclusive!";
        }

        if ($extraDetails) {
            $prompt .= " Additional special details to highlight: $extraDetails.";
        }

        return $prompt;
    }
}