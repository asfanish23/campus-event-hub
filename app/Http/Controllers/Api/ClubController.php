<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    /**
     * Get all clubs
     */
    public function index()
    {
        try {
            $clubs = Club::all();
            return response()->json([
                'data' => $clubs,
                'count' => $clubs->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch clubs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single club by ID
     */
    public function show(Club $club)
    {
        try {
            return response()->json([
                'data' => $club
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Club not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Search clubs
     */
    public function search(Request $request)
    {
        try {
            $query = $request->query('q', '');
            $category = $request->query('category');
            
            $clubs = Club::query();
            
            if (!empty($query)) {
                $clubs->where('name', 'LIKE', "%$query%")
                      ->orWhere('description', 'LIKE', "%$query%");
            }
            
            if (!empty($category) && $category !== 'All') {
                $clubs->where('category', $category);
            }
            
            $clubs = $clubs->get();
            
            return response()->json([
                'data' => $clubs,
                'count' => $clubs->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
