<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Penalty;
use App\Models\IdleSession;
use App\Models\Setting;
use App\Models\UserFile;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Get user statistics
        $totalUsers = User::count();
        $activeUsers = User::where('role', 'user')->count();
        $adminUsers = User::where('role', 'admin')->count();
        
        // Get activity statistics
        $totalActivities = ActivityLog::count();
        $todayActivities = ActivityLog::whereDate('created_at', today())->count();
        
        // Get penalty statistics
        $totalPenalties = Penalty::count();
        $activePenalties = Penalty::where('active', true)->count();
        
        // Get idle session statistics
        $totalIdleSessions = IdleSession::count();
        $todayIdleSessions = IdleSession::whereDate('created_at', today())->count();
        
        // Get file statistics
        $totalFiles = UserFile::count();
        $todayFiles = UserFile::whereDate('created_at', today())->count();
        
        // Get recent activities
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->limit(10)
            ->get();
            
        // Get recent penalties
        $recentPenalties = Penalty::with('user')
            ->latest()
            ->limit(10)
            ->get();
            
        // Get recent idle sessions
        $recentIdleSessions = IdleSession::with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers', 
            'adminUsers',
            'totalActivities',
            'todayActivities',
            'totalPenalties',
            'activePenalties',
            'totalIdleSessions',
            'todayIdleSessions',
            'totalFiles',
            'todayFiles',
            'recentActivities',
            'recentPenalties',
            'recentIdleSessions'
        ));
    }
}