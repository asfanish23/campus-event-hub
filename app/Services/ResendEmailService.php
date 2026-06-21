<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Resend;
use Throwable;

class ResendEmailService
{
    private string $apiKey;
    private string $from;

    public function __construct()
    {
        $this->apiKey = (string) config('services.resend.key');
        $this->from = 'onboarding@resend.dev';
    }

    public function sendEmail(string|array $to, string $subject, string $html): bool
    {
        if ($this->apiKey === '') {
            Log::error('Resend email send failed: RESEND_KEY is not configured.');
            return false;
        }

        try {
            $resend = Resend::client($this->apiKey);

            $resend->emails->send([
                'from' => $this->from,
                'to' => is_array($to) ? $to : [$to],
                'subject' => $subject,
                'html' => $html,
            ]);

            return true;
        } catch (Throwable $e) {
            Log::error('Resend email send failed.', [
                'to' => $to,
                'subject' => $subject,
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendPasswordResetEmail(string $to, string $name, string $resetUrl): bool
    {
        $subject = 'Reset Your Campus Event Hub Password';

        $escapedName = e($name);
        $escapedUrl = e($resetUrl);

        $html = "
            <p>Hello {$escapedName},</p>
            <p>We received a request to reset your Campus Event Hub password.</p>
            <p>
                <a href=\"{$escapedUrl}\" style=\"display:inline-block;padding:10px 16px;background:#0f766e;color:#ffffff;text-decoration:none;border-radius:6px;\">
                    Reset Password
                </a>
            </p>
            <p>If you did not request this, you can safely ignore this email.</p>
            <p>This link will expire in 60 minutes.</p>
            <p>Campus Event Hub</p>
        ";

        return $this->sendEmail($to, $subject, $html);
    }

    public function sendEventRegistrationConfirmation(string $to, string $name, string $eventName, string $eventDate, string $eventLocation): bool
    {
        $subject = 'Event Registration Confirmed';

        $escapedName = e($name);
        $escapedEventName = e($eventName);
        $escapedEventDate = e($eventDate);
        $escapedEventLocation = e($eventLocation);

        $html = "
            <p>Hello {$escapedName},</p>
            <p>Your registration for the following event has been confirmed:</p>
            <ul>
                <li><strong>Event:</strong> {$escapedEventName}</li>
                <li><strong>Date:</strong> {$escapedEventDate}</li>
                <li><strong>Location:</strong> {$escapedEventLocation}</li>
            </ul>
            <p>We look forward to seeing you there.</p>
            <p>Campus Event Hub</p>
        ";

        return $this->sendEmail($to, $subject, $html);
    }

    public function sendClubAdminApplicationSubmittedEmail(
        string $to,
        string $applicantName,
        string $applicantEmail,
        string $clubName,
        string $applicationReason,
        string $submittedAt,
        string $status = 'Pending Review'
    ): bool {
        $subject = 'Club Admin Application Submitted Successfully';

        $escapedApplicantName = e($applicantName);
        $escapedApplicantEmail = e($applicantEmail);
        $escapedClubName = e($clubName);
        $escapedApplicationReason = nl2br(e($applicationReason));
        $escapedSubmittedAt = e($submittedAt);
        $escapedStatus = e($status);

        $html = "
            <p>Hello {$escapedApplicantName},</p>
            <p>Your Club Admin application has been submitted successfully.</p>
            <ul>
                <li><strong>Applicant Name:</strong> {$escapedApplicantName}</li>
                <li><strong>Applicant Email:</strong> {$escapedApplicantEmail}</li>
                <li><strong>Selected Club:</strong> {$escapedClubName}</li>
                <li><strong>Application Reason:</strong><br>{$escapedApplicationReason}</li>
                <li><strong>Submission Date/Time:</strong> {$escapedSubmittedAt}</li>
                <li><strong>Status:</strong> {$escapedStatus}</li>
            </ul>
            <p>Our Super Admin team will review your application shortly.</p>
            <p>Campus Event Hub</p>
        ";

        return $this->sendEmail($to, $subject, $html);
    }

    public function sendClubAdminApplicationNotificationToSuperAdmin(
        string|array $to,
        string $applicantName,
        string $applicantEmail,
        string $clubName,
        string $applicationReason,
        string $submittedAt
    ): bool {
        $subject = 'New Club Admin Application Submitted';

        $escapedApplicantName = e($applicantName);
        $escapedApplicantEmail = e($applicantEmail);
        $escapedClubName = e($clubName);
        $escapedApplicationReason = nl2br(e($applicationReason));
        $escapedSubmittedAt = e($submittedAt);

        $html = "
            <p>A new Club Admin application has been submitted.</p>
            <ul>
                <li><strong>Applicant Name:</strong> {$escapedApplicantName}</li>
                <li><strong>Applicant Email:</strong> {$escapedApplicantEmail}</li>
                <li><strong>Club Name:</strong> {$escapedClubName}</li>
                <li><strong>Application Reason:</strong><br>{$escapedApplicationReason}</li>
                <li><strong>Submission Date/Time:</strong> {$escapedSubmittedAt}</li>
            </ul>
            <p>Please review this application in the Super Admin panel.</p>
            <p>Campus Event Hub</p>
        ";

        return $this->sendEmail($to, $subject, $html);
    }

    public function sendClubAdminApprovedEmail(string $to, string $applicantName, string $clubName): bool
    {
        $subject = 'Club Admin Application Approved';

        $escapedApplicantName = e($applicantName);
        $escapedClubName = e($clubName);

        $html = "
            <p>Hello {$escapedApplicantName},</p>
            <p>Your Club Admin application has been approved.</p>
            <ul>
                <li><strong>Club Name:</strong> {$escapedClubName}</li>
                <li><strong>Approval Status:</strong> Approved</li>
            </ul>
            <p>You may now access Club Admin features in Campus Event Hub.</p>
            <p>Campus Event Hub</p>
        ";

        return $this->sendEmail($to, $subject, $html);
    }

    public function sendClubAdminRejectedEmail(string $to, string $applicantName, string $clubName, ?string $rejectionReason = null): bool
    {
        $subject = 'Club Admin Application Rejected';

        $escapedApplicantName = e($applicantName);
        $escapedClubName = e($clubName);
        $escapedReason = $rejectionReason !== null && $rejectionReason !== ''
            ? nl2br(e($rejectionReason))
            : null;

        $reasonHtml = $escapedReason
            ? "<li><strong>Rejection Reason:</strong><br>{$escapedReason}</li>"
            : '';

        $html = "
            <p>Hello {$escapedApplicantName},</p>
            <p>Your Club Admin application has been reviewed.</p>
            <ul>
                <li><strong>Club Name:</strong> {$escapedClubName}</li>
                <li><strong>Rejection Status:</strong> Rejected</li>
                {$reasonHtml}
            </ul>
            <p>You may submit a new application in the future.</p>
            <p>Campus Event Hub</p>
        ";

        return $this->sendEmail($to, $subject, $html);
    }
}
