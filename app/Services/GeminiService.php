<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    
    /**
     * Menggunakan model v1beta gemini-2.0-flash untuk kepantasan 
     * dan kualiti teks yang lebih baik pada tahun 2026.
     */
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    protected $maxRetries = 3;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    public function generateEventDescription($eventName, $category, $location, $attendees = null)
    {
        $prompt = $this->buildPrompt($eventName, $category, $location, $attendees);
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

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post($this->baseUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.9,      // Lebih kreatif untuk slang student
                        'maxOutputTokens' => 2000, // Nilai tinggi supaya ayat TIDAK tergantung
                        'topP' => 0.95,
                    ]
                ]);

                if ($response->status() !== 429) break;
            }

            if ($response->failed()) {
                Log::error('Gemini API Error Detail', [
                    'status' => $response->status(),
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
                ? ['success' => true, 'text' => trim($text)] 
                : ['success' => false, 'error' => 'AI returned empty response'];

        } catch (\Exception $e) {
            Log::error('Gemini Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Connection error: ' . $e->getMessage()];
        }
    }

    private function buildPrompt($eventName, $category, $location, $attendees)
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

        return $prompt;
    }
}