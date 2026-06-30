<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentRegistrationController extends Controller
{
    public function showRegister()
    {
        return view('Auth.student-register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'student_id' => 'required|string|unique:users,student_id',
            'faculty' => 'required|string|in:' . implode(',', \App\Models\User::FACULTIES),
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $address = trim($request->address_line_1);
        if ($request->filled('address_line_2')) {
            $address .= ', ' . trim($request->address_line_2);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'student_id' => $request->student_id,
            'faculty' => $request->faculty,
            'address' => $address,
            'state' => $request->state,
            'city' => $request->city,
            'country' => 'Malaysia',
            'postal_code' => $request->postal_code,
            'password' => Hash::make($request->password),
            'role' => 'student',
        ]);

        return redirect()->route('login')->with('success', 'Registration successful! Please login with your credentials.');
    }
}
