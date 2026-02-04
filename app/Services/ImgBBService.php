<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ImgBBService
{
    /**
     * ImgBB API endpoint
     */
    private const API_URL = 'https://api.imgbb.com/1/upload';

    /**
     * ImgBB API key from config
     */
    private string $apiKey;

    /**
     * Constructor - Initialize with API key
     */
    public function __construct()
    {
        $this->apiKey = env('IMGBB_API_KEY') ?? config('services.imgbb.key') ?? '';

        if (!$this->apiKey) {
            throw new Exception('ImgBB API key not configured. Add IMGBB_API_KEY to your .env file.');
        }
    }

    /**
     * Upload an image to ImgBB and return the public URL
     *
     * @param string $imagePath The file path or URL of the image to upload
     * @param string $name Optional image name
     * 
     * @return string The public URL of the uploaded image
     * @throws Exception If upload fails
     */
    public function uploadImage(string $imagePath, string $name = 'event-image'): string
    {
        try {
            Log::info('Starting ImgBB image upload', [
                'image_path' => $imagePath,
                'name' => $name,
            ]);

            // Check if it's a local file path
            if (file_exists($imagePath)) {
                // Read the file and convert to base64
                $imageData = base64_encode(file_get_contents($imagePath));
            } else {
                // Assume it's a URL - download and convert to base64
                $imageContent = file_get_contents($imagePath);
                $imageData = base64_encode($imageContent);
            }

            // Build URL with key as query parameter (ImgBB requires this)
            $uploadUrl = self::API_URL . '?key=' . $this->apiKey;

            Log::info('ImgBB upload URL', [
                'url' => $uploadUrl,
                'image_name' => $name,
            ]);

            // Make POST request to ImgBB with form data
            $response = Http::asForm()->post($uploadUrl, [
                'image' => $imageData,
                'name' => $name,
            ]);

            Log::info('ImgBB upload response received', [
                'status' => $response->status(),
                'response_body' => $response->json(),
            ]);

            if (!$response->successful()) {
                $error = $response->json('error.message') ?? $response->body();
                Log::error('ImgBB upload failed', [
                    'error' => $error,
                    'response' => $response->json(),
                ]);
                throw new Exception("ImgBB upload failed: {$error}");
            }

            // Extract the image URL from response
            $imageUrl = $response->json('data.url');

            if (!$imageUrl) {
                Log::error('No image URL returned from ImgBB', [
                    'response' => $response->json(),
                ]);
                throw new Exception('ImgBB did not return an image URL');
            }

            Log::info('Image uploaded to ImgBB successfully', [
                'url' => $imageUrl,
            ]);

            return $imageUrl;
        } catch (Exception $e) {
            Log::error('ImgBB upload exception', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
