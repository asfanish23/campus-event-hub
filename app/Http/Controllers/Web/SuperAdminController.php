<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Club;
use App\Models\Event;
use App\Models\EventMedia;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $totalEvents = Event::count();
        $totalClubs = Club::count();
        $totalUsers = User::count();
        $totalReviews = Review::count();

        $upcomingEvents = Event::with('club')
            ->orderBy('date', 'desc')
            ->take(5)
            ->get();

        return view('super-admin.dashboard', [
            'totalEvents' => $totalEvents,
            'totalClubs' => $totalClubs,
            'totalUsers' => $totalUsers,
            'totalReviews' => $totalReviews,
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    public function manageEvents(Request $request)
    {
        $query = Event::with('club');

        // Filter by status
        if ($request->get('status') && $request->get('status') !== '') {
            $query->where('status', $request->get('status'));
        }

        // Filter by club
        if ($request->get('club') && $request->get('club') !== '') {
            $query->where('club_id', $request->get('club'));
        }

        // Search by name
        if ($request->get('search')) {
            $query->where('name', 'like', '%' . $request->get('search') . '%');
        }

        $events = $query->paginate(10);
        $clubs = Club::all();
        $statuses = ['Upcoming', 'Currently Running', 'Completed'];

        return view('super-admin.manage-events', compact('events', 'clubs', 'statuses'));
    }

    public function deleteEvent($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();
        return redirect()->route('super-admin.manage-events')->with('success', 'Event deleted successfully!');
    }

    public function updateEventStatus(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $validated = $request->validate([
            'status' => 'required|in:Upcoming,Currently Running,Completed',
        ]);

        $event->update($validated);
        return redirect()->route('super-admin.manage-events')->with('success', 'Event status updated successfully!');
    }

    public function editEvent($id)
    {
        $event = Event::findOrFail($id);
        $event->load('club');
        $clubs = Club::all();
        $statuses = ['Upcoming', 'Currently Running', 'Completed'];
        
        return view('super-admin.event-edit', compact('event', 'clubs', 'statuses'));
    }

    public function updateEvent(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'status' => 'required|in:Upcoming,Currently Running,Completed',
            'expected_attendees' => 'nullable|integer|min:0',
            'club_id' => 'nullable|exists:clubs,id',
            'event_photos' => 'nullable|array',
            'event_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            'photos_to_delete' => 'nullable|array',
            'photos_to_delete.*' => 'integer|exists:event_media,id',
        ]);

        $event->update($validated);

        // Delete photos marked for deletion
        if (!empty($validated['photos_to_delete'])) {
            foreach ($validated['photos_to_delete'] as $mediaId) {
                $media = EventMedia::find($mediaId);
                if ($media) {
                    Storage::disk('public')->delete($media->file_path);
                    $media->delete();
                }
            }
        }

        // Handle multiple photo uploads
        if ($request->hasFile('event_photos')) {
            $maxOrder = $event->media()->max('order') ?? -1;
            foreach ($request->file('event_photos') as $index => $photo) {
                $photoPath = $photo->store('event-media', 'public');
                EventMedia::create([
                    'event_id' => $event->id,
                    'file_path' => $photoPath,
                    'file_type' => 'image',
                    'order' => $maxOrder + $index + 1,
                ]);
            }
        }

        return redirect()->route('super-admin.manage-events')->with('success', 'Event updated successfully!');
    }

    public function toggleQRStatus($id)
    {
        $event = Event::findOrFail($id);
        $event->update(['qr_active' => !$event->qr_active]);
        return redirect()->route('super-admin.manage-events')->with('success', 'QR status updated successfully!');
    }

    public function showEvent($id)
    {
        $event = Event::findOrFail($id);
        $event->load('club', 'attendances', 'reviews', 'media');
        return view('super-admin.event-show', compact('event'));
    }

    public function manageClubs()
    {
        $clubs = Club::paginate(10);
        return view('super-admin.manage-clubs', ['clubs' => $clubs]);
    }

    public function createClub()
    {
        return view('super-admin.club-create');
    }

    public function storeClub(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:clubs,name',
            'email' => 'required|email|unique:clubs,email',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'profile_photo' => 'nullable|image|max:2048',
            'background_photo' => 'nullable|image|max:5120',
            'background_position_v' => 'nullable|integer|between:-100,100',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('clubs', 'public');
        }

        // Handle background photo upload
        if ($request->hasFile('background_photo')) {
            $validated['background_photo'] = $request->file('background_photo')->store('clubs', 'public');
        }

        Club::create($validated);
        return redirect()->route('super-admin.manage-clubs')->with('success', 'Club created successfully!');
    }

    public function editClub($id)
    {
        $club = Club::findOrFail($id);
        return view('super-admin.club-edit', ['club' => $club]);
    }

    public function updateClub(Request $request, $id)
    {
        $club = Club::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:clubs,name,' . $club->id,
            'email' => 'required|email|unique:clubs,email,' . $club->id,
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
            'background_photo' => 'nullable|image|max:5120',
            'background_position_v' => 'nullable|integer|between:-100,100',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            if ($club->profile_photo) {
                \Storage::disk('public')->delete($club->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('clubs', 'public');
        }

        // Handle background photo upload
        if ($request->hasFile('background_photo')) {
            if ($club->background_photo) {
                \Storage::disk('public')->delete($club->background_photo);
            }
            $validated['background_photo'] = $request->file('background_photo')->store('clubs', 'public');
        }

        $club->update($validated);
        return redirect()->route('super-admin.manage-clubs')->with('success', 'Club updated successfully!');
    }

    public function deleteClub($id)
    {
        $club = Club::findOrFail($id);
        $clubName = $club->name;
        $club->delete();
        return redirect()->route('super-admin.manage-clubs')->with('success', 'Club ' . $clubName . ' has been deleted successfully!');
    }

    public function showClub($id)
    {
        $club = Club::findOrFail($id);
        return view('super-admin.club-show', ['club' => $club]);
    }

    public function manageUsers()
    {
        $pendingApplications = User::where('role', 'admin')
            ->where('admin_status', 'pending')
            ->with('club')
            ->orderBy('admin_submitted_at', 'desc')
            ->get();

        $approvedAdmins = User::where('role', 'admin')
            ->where('admin_status', 'approved')
            ->with('club')
            ->paginate(10);

        $allUsers = User::with('club')->paginate(15);

        return view('super-admin.manage-users', [
            'pendingApplications' => $pendingApplications,
            'approvedAdmins' => $approvedAdmins,
            'allUsers' => $allUsers,
        ]);
    }

    public function manageReviews()
    {
        $reviews = Review::with('event', 'user')->paginate(10);
        return view('super-admin.manage-reviews', ['reviews' => $reviews]);
    }

    public function systemSettings()
    {
        return view('super-admin.system-settings');
    }

    public function createEvent()
    {
        $clubs = Club::all();
        return view('super-admin.event-create', ['clubs' => $clubs]);
    }

    public function storeEvent(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'location' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'status' => 'required|in:Upcoming,Currently Running,Completed',
            'expected_attendees' => 'nullable|integer|min:0',
            'club_id' => 'required|exists:clubs,id',
            'event_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'event_photos' => 'nullable|array',
            'event_photos.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        // Handle main image upload
        if ($request->hasFile('event_image')) {
            $imagePath = $request->file('event_image')->store('events', 'public');
            $validated['event_image'] = $imagePath;
        }

        $event = Event::create($validated);

        // Handle multiple photo uploads
        if ($request->hasFile('event_photos')) {
            foreach ($request->file('event_photos') as $index => $photo) {
                $photoPath = $photo->store('event-media', 'public');
                EventMedia::create([
                    'event_id' => $event->id,
                    'file_path' => $photoPath,
                    'file_type' => 'image',
                    'order' => $index,
                ]);
            }
        }

        return redirect()->route('super-admin.manage-events')->with('success', 'Event created successfully!');
    }

    public function approveAdmin(User $user)
    {
        $user->update(['admin_status' => 'approved']);
        
        // Send approval email
        try {
            \Mail::send('emails.admin-approved', ['user' => $user], function ($message) use ($user) {
                $message->to($user->email)->subject('Admin Application Approved');
            });
            $message = 'Admin application approved! Email sent to ' . $user->email;
        } catch (\Exception $e) {
            $message = 'Admin application approved! (Email could not be sent - ' . $e->getMessage() . ')';
        }

        return redirect()->route('super-admin.manage-users')->with('success', $message);
    }

    public function rejectAdmin(Request $request, User $user)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10',
        ]);

        $user->update(['admin_status' => 'rejected']);
        
        // Send rejection email
        try {
            \Mail::send('emails.admin-rejected', [
                'user' => $user,
                'reason' => $request->rejection_reason
            ], function ($message) use ($user) {
                $message->to($user->email)->subject('Admin Application Rejected');
            });
            $message = 'Admin application rejected! Email sent to ' . $user->email;
        } catch (\Exception $e) {
            $message = 'Admin application rejected! (Email could not be sent - ' . $e->getMessage() . ')';
        }

        return redirect()->route('super-admin.manage-users')->with('success', $message);
    }

    public function deleteUser(User $user)
    {
        // Prevent deletion of super admin accounts
        if ($user->role === 'super_admin') {
            return redirect()->route('super-admin.manage-users')->with('error', 'Super Admin accounts cannot be deleted!');
        }

        $userName = $user->name;
        $user->delete();
        return redirect()->route('super-admin.manage-users')->with('success', 'User ' . $userName . ' has been deleted successfully!');
    }

    public function updateUserRole(Request $request, User $user)
    {
        // Prevent changing super admin role
        if ($user->role === 'super_admin') {
            return redirect()->route('super-admin.manage-users')->with('error', 'Cannot change Super Admin role!');
        }

        $request->validate([
            'role' => 'required|in:student,admin',
        ]);

        $oldRole = $user->role;
        $newRole = $request->role;

        $user->update([
            'role' => $newRole,
            'admin_status' => null, // Clear admin status if changing roles
        ]);

        return redirect()->route('super-admin.manage-users')->with('success', 'Role updated! ' . $user->name . ' role changed from ' . ucfirst($oldRole) . ' to ' . ucfirst($newRole));
    }
}
