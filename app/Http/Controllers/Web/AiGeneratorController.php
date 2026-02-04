<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class AiGeneratorController extends Controller
{
    protected $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
        $this->middleware('auth');
    }

    /**
     * Generate event description
     */
    public function generateDescription(Request $request)
    {
        $request->validate([
            'event_name' => 'required|string',
            'category' => 'required|string',
            'location' => 'required|string',
            'attendees' => 'nullable|integer'
        ]);

        $result = $this->geminiService->generateEventDescription(
            $request->event_name,
            $request->category,
            $request->location,
            $request->attendees
        );

        return response()->json($result);
    }

    /**
     * Tweak existing description
     */
    public function tweakDescription(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
            'style' => 'required|in:funnier,professional,shorter'
        ]);

        $result = $this->geminiService->tweakDescription(
            $request->text,
            $request->style
        );

        return response()->json($result);
    }
}
