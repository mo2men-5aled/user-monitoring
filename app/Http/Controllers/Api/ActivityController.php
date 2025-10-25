<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function ping(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Log the activity ping
        ActivityLogService::log('activity_ping');
        
        return response()->json(['message' => 'Activity ping received']);
    }
}
