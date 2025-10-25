<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penalty;
use App\Models\User;
use App\Services\PenaltyService;

class PenaltyController extends Controller
{
    public function index()
    {
        $penalties = Penalty::with('user')->latest()->paginate(10);
        return view('admin.penalties.index', compact('penalties'));
    }

    public function deactivate(Penalty $penalty)
    {
        $penalty->update(['active' => false]);
        
        return redirect()->back()->with('success', 'Penalty deactivated successfully.');
    }

    public function clearUserPenalties(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        PenaltyService::deactivateAllPenalties($user->id);
        
        return redirect()->back()->with('success', 'All penalties for user cleared successfully.');
    }
}