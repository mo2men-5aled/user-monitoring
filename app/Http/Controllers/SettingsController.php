<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::orderBy('key')->get();
        return view('admin.settings.index', compact('settings'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Setting::create($validator->validated());

        return redirect()->route('admin.settings.index')->with('success', 'Setting created successfully.');
    }

    public function update(Request $request, Setting $setting)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|string',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $setting->update($validator->validated());

        return redirect()->route('admin.settings.index')->with('success', 'Setting updated successfully.');
    }

    public function destroy(Setting $setting)
    {
        $setting->delete();

        return redirect()->route('admin.settings.index')->with('success', 'Setting deleted successfully.');
    }
    
    /**
     * Special method to get the idle timeout setting
     */
    public function getIdleTimeout()
    {
        $timeout = Setting::where('key', 'idle_timeout')->first();
        
        if (!$timeout) {
            // Return default value if setting doesn't exist
            return response()->json(['timeout' => 300]); // 5 minutes default
        }
        
        return response()->json(['timeout' => (int)$timeout->value]);
    }
    
    /**
     * Special method to get the monitoring status
     */
    public function getMonitoringStatus()
    {
        $enabled = Setting::where('key', 'monitoring_enabled')->first();
        
        if (!$enabled) {
            // Return default value if setting doesn't exist
            return response()->json(['enabled' => true]);
        }
        
        return response()->json(['enabled' => filter_var($enabled->value, FILTER_VALIDATE_BOOLEAN)]);
    }
}