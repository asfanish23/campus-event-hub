<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\StudentEventRegistration;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StudentProfileController extends Controller
{
    /**
     * GET /student/profile
     * Show student profile with tabs for different sections
     */
    public function show()
    {
        $user = auth()->user();
        
        // Get user's data
        $registrations = $user->registrations()->with('event')->latest()->get();
        $orders = $user->orders()->with('product')->latest()->get();
        $payments = $user->payments()->latest()->get();

        return view('student.profile.show', compact('user', 'registrations', 'orders', 'payments'));
    }

    /**
     * GET /student/profile/edit
     * Show edit profile form
     */
    public function edit()
    {
        $user = auth()->user();
        return view('student.profile.edit', compact('user'));
    }

    /**
     * POST /student/profile/update
     * Update user profile information
     */
    public function update(Request $request)
    {
        try {
            $user = auth()->user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'bio' => 'nullable|string|max:500',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
            ]);

            $user->update($validated);

            Log::info('Profile Updated', ['user_id' => $user->id]);

            return redirect()->route('student.profile.show')
                ->with('success', 'Profile updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Profile Update Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred while updating your profile.');
        }
    }

    /**
     * POST /student/profile/upload-photo
     * Upload profile photo
     */
    public function uploadPhoto(Request $request)
    {
        try {
            $validated = $request->validate([
                'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            $user = auth()->user();

            // Delete old photo if exists
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }

            // Store new photo
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            
            $user->update(['profile_photo' => $path]);

            Log::info('Profile Photo Uploaded', ['user_id' => $user->id, 'path' => $path]);

            return back()->with('success', 'Profile photo updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Photo Upload Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'An error occurred while uploading the photo.');
        }
    }

    /**
     * GET /student/profile/registrations
     * View event registrations
     */
    public function registrations()
    {
        $user = auth()->user();
        $registrations = $user->registrations()
            ->with('event')
            ->latest()
            ->paginate(10);

        return view('student.profile.registrations', compact('registrations'));
    }

    /**
     * GET /student/profile/cart
     * View shopping cart (stored in session/localStorage)
     */
    public function cart()
    {
        // Cart is typically stored in session or localStorage on client
        // This view will display cart items from localStorage
        return view('student.profile.cart');
    }

    /**
     * GET /student/profile/orders
     * View user's orders
     */
    public function orders()
    {
        $user = auth()->user();
        $orders = $user->orders()
            ->with('product')
            ->latest()
            ->paginate(10);

        return view('student.profile.orders', compact('orders'));
    }

    /**
     * GET /student/profile/payments
     * View payment history
     */
    public function payments()
    {
        $user = auth()->user();
        $payments = $user->payments()
            ->latest()
            ->paginate(10);

        return view('student.profile.payments', compact('payments'));
    }
}
