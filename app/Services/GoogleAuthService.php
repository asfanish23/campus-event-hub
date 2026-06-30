<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;
use Exception;

class GoogleAuthService
{
    /**
     * Process Google User information for linking/creation.
     *
     * @param \Laravel\Socialite\Contracts\User|\Laravel\Socialite\Two\User $googleUser
     * @param string|null $faculty
     * @param bool $allowAutoCreate
     * @return array
     * @throws Exception
     */
    public function processGoogleUser($googleUser, ?string $faculty = null, bool $allowAutoCreate = true): array
    {
        $googleId = $googleUser->getId();
        $email = $googleUser->getEmail();
        $name = $googleUser->getName();
        $avatar = $googleUser->getAvatar();

        // Find existing user by google_id or email
        $user = User::where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            // Check if the user is a student (Google Login is student-only)
            if ($user->role !== 'student') {
                throw new Exception('Google Sign-In is only available for student accounts.');
            }

            // Update Google authentication info for existing student
            $user->update([
                'google_id' => $googleId,
                'avatar' => $user->avatar ?? $avatar,
                'provider' => 'google',
            ]);

            return [
                'status' => 'success',
                'user' => $user,
            ];
        }

        // If the user does not exist and auto-creation is disabled (Mobile Login check)
        if (!$allowAutoCreate && !$faculty) {
            return [
                'status' => 'needs_registration',
                'email' => $email,
                'name' => $name,
                'google_id' => $googleId,
                'avatar' => $avatar,
            ];
        }

        // Register a new user as a student
        $studentId = 'STU' . strtoupper(substr(md5(time() . $email), 0, 8));

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'google_id' => $googleId,
            'avatar' => $avatar,
            'provider' => 'google',
            'password' => bcrypt(Str::random(24)),
            'role' => 'student',
            'student_id' => $studentId,
            'faculty' => $faculty,
        ]);

        return [
            'status' => 'success',
            'user' => $user,
        ];
    }
}
