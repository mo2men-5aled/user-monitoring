<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdleSession;
use App\Services\PenaltyService;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class InactivityController extends Controller
{
    public function storePenalty(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $request->validate([
            'reason' => 'required|string|max:255',
            'level' => 'required|integer|min:1|max:3',
        ]);
        
        // Create idle session record
        $idleSession = IdleSession::create([
            'user_id' => $user->id,
            'start_time' => now(),
            'session_id' => session()->getId(),
        ]);
        
        // Use PenaltyService to handle the penalty
        $penalty = PenaltyService::handleRepeatedIdleSession($user->id, $idleSession->id);
        
        return response()->json([
            'message' => 'Penalty applied successfully',
            'penalty' => $penalty
        ]);
    }
}
