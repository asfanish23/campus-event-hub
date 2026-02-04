<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    
    /**
     * Menggunakan model v1beta gemini-2.5-flash untuk kepantasan 
     * dan kualiti teks yang lebih baik pada tahun 2026.
     */
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    protected $maxRetries = 3;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
    }

    public function generateEventDescription($eventName, $category, $location, $attendees = null, $extraDetails = null)
    {
        $prompt = $this->buildPrompt($eventName, $category, $location, $attendees, $extraDetails);
        return $this->callGemini($prompt);
    }

    public function tweakDescription($currentText, $style)
    {
        $instructions = [
            'official' => 'Rewrite this event description in a formal, structured announcement format. Include: Assalamualaikum greeting, organized sections with emojis for visual hierarchy (not scattered), clear bullet points for key info (tarikh, masa, tempat), professional tone without slang, and contact details. Format like official university announcements.',
            'fun' => 'Rewrite this event description in a fun, energetic style while keeping the structured format. Use Malaysian student slang (Manglish) with words like "mantap", "gempak", "onsz". Add more casual emojis throughout. Keep it organized with sections but make it sound exciting and playful for students.',
            'shorter' => 'Summarize this into a very short and punchy social media caption (max 50 words).'
        ];

        $selectedInstruction = $instructions[$style] ?? $instructions['official'];
        $prompt = "Current Description: $currentText\n\nInstruction: $selectedInstruction\n\nEnsure the response is a complete paragraph and properly formatted.";

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

    private function buildPrompt($eventName, $category, $location, $attendees, $extraDetails = null)
    {
        $prompt = "You are a pro event announcement writer for a campus club. ";
        $prompt .= "Task: Write a formal, structured event announcement for: '$eventName'. ";
        $prompt .= "Format: Use official announcement style with Assalamualaikum greeting, clear sections (tarikh, masa, tempat, info), organized bullet points, and emojis for visual hierarchy. ";
        $prompt .= "Tone: Professional and respectful, suitable for university official announcements. Keep it organized and easy to read. ";
        $prompt .= "Style: NO Malaysian student slang, formal tone, clear contact details format. Include a proper closing. DO NOT stop mid-sentence.";
        
        if ($attendees) {
            $prompt .= $attendees > 100 
                ? " Highlight that this is a large-scale event with many opportunities." 
                : " Highlight the limited and exclusive nature of this event.";
        }

        if ($extraDetails) {
            $prompt .= " Additional details to highlight: $extraDetails.";
        }

        return $prompt;
    }
}