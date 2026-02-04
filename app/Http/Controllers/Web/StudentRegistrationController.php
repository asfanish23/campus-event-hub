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
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'student_id' => $request->student_id,
            'address' => $request->address,
            'city' => $request->city,
            'postal_code' => $request->postal_code,
            'password' => Hash::make($request->password),
            'role' => 'student',
            'student_id' => $request->student_id,
        ]);

        return redirect()->route('login')->with('success', 'Registration successful! Please login with your credentials.');
    }
}
