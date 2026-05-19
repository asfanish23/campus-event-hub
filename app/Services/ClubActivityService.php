<?php

namespace App\Services;

use App\Models\Club;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class ClubActivityService
{
    public const INACTIVE_MESSAGE = 'Your club is currently inactive. Please contact HEP.';

    public function recordClubActivity(?Club $club): void
    {
        if (! $club) {
            return;
        }

        $club->forceFill([
            'last_activity_at' => now(),
        ])->save();
    }

    public function recordUserActivity(?User $user): void
    {
        if (! $user || ! $user->club_id || $user->role !== 'admin') {
            return;
        }

        $this->recordClubActivity($user->club);
    }

    public function ensureClubIsActive(?Club $club): void
    {
        if (! $club || $club->status === Club::STATUS_ACTIVE) {
            return;
        }

        throw ValidationException::withMessages([
            'club' => self::INACTIVE_MESSAGE,
        ]);
    }
}