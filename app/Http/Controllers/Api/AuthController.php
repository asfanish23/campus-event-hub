<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        \Log::info('Register endpoint called', ['path' => $request->getPathInfo(), 'method' => $request->getMethod()]);
        
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6'
            ]);
            \Log::info('Validation passed', ['validated' => $validated]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            \Log::info('User created', ['user_id' => $user->id]);

            $token = $user->createToken('auth_token')->plainTextToken;
            \Log::info('Token created');

            $response = response()->json([
                'user' => $user,
                'token' => $token
            ], 201);
            
            \Log::info('Response created', ['status' => 201]);
            return $response;
        } catch (\Exception $e) {
            \Log::error('Register error', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }
}
