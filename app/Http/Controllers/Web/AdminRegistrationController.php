<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminRegistrationController extends Controller
{
    public function showRegister()
    {
        $clubs = Club::all();
        return view('auth.admin-register', ['clubs' => $clubs]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'club_id' => 'required|exists:clubs,id',
            'reason' => 'required|string|min:10',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin',
            'club_id' => $request->club_id,
            'admin_status' => 'pending',
            'admin_application_reason' => $request->reason,
            'admin_submitted_at' => now(),
        ]);

        return redirect()->route('admin-register')->with('success', 'Application submitted successfully! Please wait for approval.');
    }
}
