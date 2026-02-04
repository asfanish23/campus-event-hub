<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClubProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $club = Club::find($user->club_id) ?? new Club();
        return view('club-profile.show', compact('club'));
    }

    public function edit()
    {
        $user = Auth::user();
        $club = Club::find($user->club_id) ?? new Club();
        return view('club-profile.edit', compact('club'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $club = Club::find($user->club_id);
        
        if (!$club) {
            return back()->with('error', 'Club not found!');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'president_name' => 'required|string|max:255',
            'president_contact' => 'required|string|max:20',
            'facebook_url' => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'twitter_url' => 'nullable|string|max:255',
            'total_members' => 'nullable|integer|min:0',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'background_position_v' => 'nullable|integer|min:-100|max:100',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo if it exists
            if ($club->profile_photo) {
                $oldPath = public_path('storage/' . $club->profile_photo);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // Store new photo
            $photoPath = $request->file('profile_photo')->store('club-photos', 'public');
            $validated['profile_photo'] = $photoPath;
        }

        // Handle background photo upload
        if ($request->hasFile('background_photo')) {
            // Delete old background if it exists
            if ($club->background_photo) {
                $oldPath = public_path('storage/' . $club->background_photo);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // Store new background
            $backgroundPath = $request->file('background_photo')->store('club-backgrounds', 'public');
            $validated['background_photo'] = $backgroundPath;
            // Reset positioning when uploading new photo
            $validated['background_position_v'] = 0;
        }

        $club->update($validated);
        return back()->with('success', 'Club profile updated successfully!');
    }
}
