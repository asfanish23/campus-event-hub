<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Club;
use App\Services\ResendEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminRegistrationController extends Controller
{
    public function __construct(private readonly ResendEmailService $resendEmailService)
    {
    }

    public function showRegister()
    {
        $clubs = Club::all();
        return view('Auth.admin-register', ['clubs' => $clubs]);
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

        $clubName = Club::where('id', $request->club_id)->value('name') ?? 'Unknown Club';
        $submittedAt = optional($user->admin_submitted_at)->format('d M Y H:i:s') ?? now()->format('d M Y H:i:s');

        Log::info('Club admin application submitted', [
            'user_id' => $user->id,
            'email' => $user->email,
            'club_id' => $user->club_id,
            'club_name' => $clubName,
        ]);

        try {
            $applicantEmailSent = $this->resendEmailService->sendClubAdminApplicationSubmittedEmail(
                $user->email,
                $user->name,
                $user->email,
                $clubName,
                (string) $user->admin_application_reason,
                $submittedAt,
                'Pending Review'
            );

            if ($applicantEmailSent) {
                Log::info('Club admin application email sent', [
                    'recipient_type' => 'applicant',
                    'email' => $user->email,
                    'user_id' => $user->id,
                ]);
            } else {
                Log::warning('Club admin email failed', [
                    'recipient_type' => 'applicant',
                    'email' => $user->email,
                    'user_id' => $user->id,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Club admin email failed', [
                'recipient_type' => 'applicant',
                'email' => $user->email,
                'user_id' => $user->id,
                'error_message' => $e->getMessage(),
            ]);
        }

        $superAdminEmails = User::where('role', 'super_admin')
            ->pluck('email')
            ->filter()
            ->values()
            ->all();

        if (!empty($superAdminEmails)) {
            try {
                $superAdminEmailSent = $this->resendEmailService->sendClubAdminApplicationNotificationToSuperAdmin(
                    $superAdminEmails,
                    $user->name,
                    $user->email,
                    $clubName,
                    (string) $user->admin_application_reason,
                    $submittedAt
                );

                if ($superAdminEmailSent) {
                    Log::info('Club admin application email sent', [
                        'recipient_type' => 'super_admin',
                        'recipient_count' => count($superAdminEmails),
                    ]);
                } else {
                    Log::warning('Club admin email failed', [
                        'recipient_type' => 'super_admin',
                        'recipient_count' => count($superAdminEmails),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Club admin email failed', [
                    'recipient_type' => 'super_admin',
                    'recipient_count' => count($superAdminEmails),
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return redirect()->route('admin-register')->with('success', 'Application submitted successfully! Please wait for approval.');
    }
}
