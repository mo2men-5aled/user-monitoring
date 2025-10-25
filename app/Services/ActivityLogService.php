<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class ActivityLogService
{
    public static function log(string $action, ?string $modelType = null, ?int $modelId = null, array $metadata = []): ActivityLog
    {
        $user = Auth::user();
        
        return ActivityLog::create([
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'metadata' => $metadata,
        ]);
    }

    public static function logLogin(): ActivityLog
    {
        return self::log('login');
    }

    public static function logLogout(): ActivityLog
    {
        return self::log('logout');
    }

    public static function logCreate(string $modelType, int $modelId, array $metadata = []): ActivityLog
    {
        return self::log('create', $modelType, $modelId, $metadata);
    }

    public static function logRead(string $modelType, int $modelId, array $metadata = []): ActivityLog
    {
        return self::log('read', $modelType, $modelId, $metadata);
    }

    public static function logUpdate(string $modelType, int $modelId, array $metadata = []): ActivityLog
    {
        return self::log('update', $modelType, $modelId, $metadata);
    }

    public static function logDelete(string $modelType, int $modelId, array $metadata = []): ActivityLog
    {
        return self::log('delete', $modelType, $modelId, $metadata);
    }

    public static function logUpload(string $modelType, int $modelId, array $metadata = []): ActivityLog
    {
        return self::log('upload', $modelType, $modelId, $metadata);
    }

    public static function logDownload(string $modelType, int $modelId, array $metadata = []): ActivityLog
    {
        return self::log('download', $modelType, $modelId, $metadata);
    }

    public static function logApproval(string $modelType, int $modelId, array $metadata = []): ActivityLog
    {
        return self::log('approval', $modelType, $modelId, $metadata);
    }
}