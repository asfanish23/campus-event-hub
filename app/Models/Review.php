<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_id',
        'reviewer_name',
        'rating',
        'comment',
        'review_text',
        'is_reported',
        'reported_by_admin_id',
        'reported_at',
    ];

    protected $casts = [
        'is_reported' => 'boolean',
        'reported_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reportedByAdmin()
    {
        return $this->belongsTo(User::class, 'reported_by_admin_id');
    }

    public function getCommentAttribute($value): ?string
    {
        return $value ?? $this->attributes['review_text'] ?? null;
    }

    public function getDisplayReviewerNameAttribute(): string
    {
        return $this->user?->name
            ?? $this->reviewer_name
            ?? 'Student';
    }

    public static function buildEligibility(User $user, Event $event): array
    {
        $hasJoined = StudentEventRegistration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->exists();

        $hasAttendance = Attendance::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->where('status', 'Present')
            ->exists();

        $isCompleted = $event->getComputedStatus() === 'completed';

        $existingReview = static::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        return [
            'has_joined' => $hasJoined,
            'has_attendance' => $hasAttendance,
            'event_completed' => $isCompleted,
            'already_reviewed' => $existingReview !== null,
            'can_submit_review' => $hasJoined && $hasAttendance && $isCompleted && $existingReview === null,
            'existing_review' => $existingReview,
        ];
    }

    public static function toApiPayload(Review $review): array
    {
        return [
            'id' => $review->id,
            'student_name' => $review->display_reviewer_name,
            'rating' => (int) $review->rating,
            'comment' => $review->comment,
            'review_date' => optional($review->created_at)->toDateString(),
            'created_at' => optional($review->created_at)->toIso8601String(),
            'is_reported' => (bool) $review->is_reported,
            'reported_at' => $review->reported_at instanceof Carbon
                ? $review->reported_at->toIso8601String()
                : null,
        ];
    }
}
