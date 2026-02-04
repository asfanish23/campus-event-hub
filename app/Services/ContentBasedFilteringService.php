<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Collection;

class ContentBasedFilteringService
{
    /**
     * Check if event is upcoming
     * Handles both Carbon and string date formats
     * Uses Carbon comparison to avoid date format issues
     */
    private function isEventUpcoming(Event $event): bool
    {
        $eventDate = is_string($event->date) ? \Carbon\Carbon::parse($event->date) : $event->date;
        return $eventDate->isToday() || $eventDate->isFuture();
    }

    /**
     * Get event features as a vector
     * Features include: category, club_id, and other attributes
     */
    private function getEventFeatures(Event $event): array
    {
        return [
            'category' => $event->category,
            'club_id' => $event->club_id,
            'location' => $event->location,
            'status' => $event->status,
            // Temporal features
            'is_upcoming' => $this->isEventUpcoming($event) ? 1 : 0,
        ];
    }

    /**
     * Calculate similarity between two events (0 to 1)
     * Uses feature matching
     */
    private function calculateEventSimilarity(Event $event1, Event $event2): float
    {
        $features1 = $this->getEventFeatures($event1);
        $features2 = $this->getEventFeatures($event2);

        $similarities = [];
        $weights = [
            'category' => 0.35,      // Category is most important
            'club_id' => 0.25,       // Club matters
            'location' => 0.15,      // Location similarity
            'status' => 0.10,        // Status (published, etc)
            'is_upcoming' => 0.15,   // Temporal relevance
        ];

        foreach ($weights as $feature => $weight) {
            $similarity = $features1[$feature] === $features2[$feature] ? 1 : 0;
            $similarities[$feature] = $similarity * $weight;
        }

        return array_sum($similarities);
    }

    /**
     * Build user profile based on liked events
     * Returns weighted features of liked events
     */
    private function buildUserProfile(User $user): array
    {
        $likedEvents = $user->likedEvents()->get();

        if ($likedEvents->isEmpty()) {
            return [];
        }

        $categoryScores = [];
        $clubScores = [];
        $locationScores = [];

        foreach ($likedEvents as $event) {
            // Increment category scores
            $categoryScores[$event->category] = ($categoryScores[$event->category] ?? 0) + 1;
            
            // Increment club scores
            $clubScores[$event->club_id] = ($clubScores[$event->club_id] ?? 0) + 1;
            
            // Increment location scores
            $locationScores[$event->location] = ($locationScores[$event->location] ?? 0) + 1;
        }

        return [
            'categories' => $categoryScores,
            'clubs' => $clubScores,
            'locations' => $locationScores,
            'total_likes' => $likedEvents->count(),
        ];
    }

    /**
     * Calculate relevance score of an event to user profile (0 to 1)
     */
    private function calculateUserEventRelevance(User $user, Event $event, array $userProfile): float
    {
        if (empty($userProfile)) {
            // No preference data, give base score
            return 0.3;
        }

        $relevance = 0;

        // Category match (highest weight)
        if (isset($userProfile['categories'][$event->category])) {
            $categoryWeight = $userProfile['categories'][$event->category] / $userProfile['total_likes'];
            $relevance += 0.5 * $categoryWeight;
        }

        // Club match
        if (isset($userProfile['clubs'][$event->club_id])) {
            $clubWeight = $userProfile['clubs'][$event->club_id] / $userProfile['total_likes'];
            $relevance += 0.3 * $clubWeight;
        }

        // Location match
        if (isset($userProfile['locations'][$event->location])) {
            $locationWeight = $userProfile['locations'][$event->location] / $userProfile['total_likes'];
            $relevance += 0.2 * $locationWeight;
        }

        // Normalize to 0-1
        return min(1, $relevance);
    }

    /**
     * Content-based filtering: Recommend events similar to liked events
     */
    public function getRecommendations(User $user, int $limit = 10): Collection
    {
        // Get events user has already liked (fetch once)
        $likedEventIds = $user->likedEvents()->pluck('id')->toArray();

        // Get events user has attended
        $attendedEventIds = $user->studentEventRegistrations()->pluck('event_id')->toArray();

        // Get events user hasn't liked or attended (exclude completed events)
        $availableEvents = Event::whereNotIn('id', array_merge($likedEventIds, $attendedEventIds))
            ->whereRaw("LOWER(status) != ?", ['completed'])
            ->whereDate('date', '>=', now())
            ->get();

        // Build user profile from likes
        $userProfile = $this->buildUserProfile($user);

        // Fetch all liked events once (for similarity calculation)
        $likedEvents = $user->likedEvents()->get();

        // Score each event
        $scoredEvents = [];

        foreach ($availableEvents as $event) {
            // Calculate relevance based on user profile
            $profileRelevance = $this->calculateUserEventRelevance($user, $event, $userProfile);

            // If user has no likes yet, give equal weight to all events
            if (empty($userProfile)) {
                $similarity = 0.5;
            } else {
                // Calculate average similarity to liked events
                $similarities = [];

                foreach ($likedEvents as $likedEvent) {
                    $similarities[] = $this->calculateEventSimilarity($likedEvent, $event);
                }

                $similarity = !empty($similarities) ? array_sum($similarities) / count($similarities) : 0.5;
            }

            // Combined score: profile relevance + content similarity
            $finalScore = (0.6 * $profileRelevance) + (0.4 * $similarity);

            $scoredEvents[] = [
                'event' => $event,
                'score' => $finalScore,
            ];
        }

        // Sort by score descending
        usort($scoredEvents, fn($a, $b) => $b['score'] <=> $a['score']);

        // Return top N events
        return collect($scoredEvents)
            ->take($limit)
            ->map(fn($item) => $item['event']);
    }

    /**
     * Get similar events to a given event
     */
    public function getSimilarEvents(Event $event, int $limit = 5): Collection
    {
        $allEvents = Event::where('id', '!=', $event->id)
            ->whereRaw("LOWER(status) != ?", ['completed'])
            ->whereDate('date', '>=', now())
            ->get();

        $scoredEvents = [];

        foreach ($allEvents as $compareEvent) {
            $similarity = $this->calculateEventSimilarity($event, $compareEvent);
            $scoredEvents[] = [
                'event' => $compareEvent,
                'score' => $similarity,
            ];
        }

        // Sort by score descending
        usort($scoredEvents, fn($a, $b) => $b['score'] <=> $a['score']);

        return collect($scoredEvents)
            ->take($limit)
            ->map(fn($item) => $item['event']);
    }
}
