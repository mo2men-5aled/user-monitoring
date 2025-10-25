<?php

namespace App\Services;

use App\Models\Penalty;
use App\Models\IdleSession;
use Illuminate\Support\Facades\Auth;

class PenaltyService
{
    /**
     * Apply a penalty to a user
     */
    public static function applyPenalty(int $userId, string $reason, int $penaltyCount = 1): Penalty
    {
        return Penalty::create([
            'user_id' => $userId,
            'reason' => $reason,
            'penalty_count' => $penaltyCount,
            'active' => true,
        ]);
    }

    /**
     * Check if user has active penalties
     */
    public static function hasActivePenalties(int $userId): bool
    {
        return Penalty::where('user_id', $userId)
            ->where('active', true)
            ->exists();
    }

    /**
     * Get all active penalties for a user
     */
    public static function getActivePenalties(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Penalty::where('user_id', $userId)
            ->where('active', true)
            ->get();
    }

    /**
     * Count active penalties for a user
     */
    public static function countActivePenalties(int $userId): int
    {
        return Penalty::where('user_id', $userId)
            ->where('active', true)
            ->count();
    }

    /**
     * Deactivate all penalties for a user
     */
    public static function deactivateAllPenalties(int $userId): void
    {
        Penalty::where('user_id', $userId)
            ->where('active', true)
            ->update(['active' => false]);
    }

    /**
     * Handle repeated idle sessions by applying escalating penalties
     */
    public static function handleRepeatedIdleSession(int $userId, int $idleSessionId): Penalty
    {
        // Count how many active penalties the user currently has
        $currentActivePenalties = self::countActivePenalties($userId);
        
        // The penalty count will be one more than current active penalties
        $penaltyCount = $currentActivePenalties + 1;
        
        $reason = "Excessive idle time - " . match($penaltyCount) {
            1 => "First idle session",
            2 => "Second idle session", 
            3 => "Third idle session - automatic logout applied",
            default => "Repeated idle session #{$penaltyCount}",
        };
        
        // Create the new penalty
        $penalty = self::applyPenalty($userId, $reason, $penaltyCount);
        
        // Link the penalty to the idle session if provided
        if ($idleSessionId) {
            // We could create a relationship here if needed
            // For now, just log it as metadata
            \App\Services\ActivityLogService::log('penalty_applied', Penalty::class, $penalty->id, [
                'reason' => $reason,
                'penalty_count' => $penaltyCount,
                'idle_session_id' => $idleSessionId,
            ]);
        }
        
        // If this is the third penalty, we might want to enforce additional consequences
        if ($penaltyCount >= 3) {
            // Additional actions for severe penalties could be implemented here
            // For example, account suspension, extended timeout, etc.
        }
        
        return $penalty;
    }

    /**
     * Clear expired penalties
     */
    public static function clearExpiredPenalties(): void
    {
        Penalty::where('active', true)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['active' => false]);
    }
}