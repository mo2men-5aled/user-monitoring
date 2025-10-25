<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ActivityLogService;

class ActivityLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log specific actions
        $this->logActivity($request, $response);

        return $response;
    }

    private function logActivity(Request $request, Response $response): void
    {
        // Only log if user is authenticated
        if (!Auth::check()) {
            return;
        }

        $method = $request->method();
        $route = $request->route();
        
        // If route name exists, we can log it
        if ($route && $this->shouldLogRoute($route->getName())) {
            $action = $this->getActionFromMethodAndRoute($method, $route->getName());
            if ($action) {
                ActivityLogService::log($action);
            }
        }
    }

    private function shouldLogRoute(?string $routeName): bool
    {
        if (!$routeName) {
            return false;
        }

        // Don't log these routes
        $excludedRoutes = [
            'login',
            'logout',
            'register',
            'password.*', // Don't log password related routes
            'verification.*', // Don't log verification related routes
            'admin.activity-logs.*', // Don't log activity logs routes to prevent recursion
        ];

        foreach ($excludedRoutes as $excluded) {
            if ($this->routeMatchesPattern($excluded, $routeName)) {
                return false;
            }
        }

        return true;
    }
    
    private function routeMatchesPattern(string $pattern, string $routeName): bool
    {
        // Convert wildcard pattern to regex
        $pattern = preg_quote($pattern, '/');
        $pattern = str_replace('\*', '.*', $pattern);
        return preg_match('/^' . $pattern . '$/', $routeName);
    }

    private function getActionFromMethodAndRoute(string $method, string $routeName): ?string
    {
        // Map specific route names to actions
        switch ($routeName) {
            case 'profile.update':
                return 'profile_update';
            case 'profile.destroy':
                return 'profile_delete';
            default:
                // Map HTTP methods to actions
                switch ($method) {
                    case 'POST':
                        return 'create';
                    case 'PUT':
                    case 'PATCH':
                        return 'update';
                    case 'DELETE':
                        return 'delete';
                    case 'GET':
                        // Only log GET requests for specific routes
                        if (str_contains($routeName, '.edit') || 
                            str_contains($routeName, '.create') ||
                            str_contains($routeName, '.show')) {
                            return 'view';
                        }
                        return null; // Don't log regular GET requests (views, lists)
                    default:
                        return null;
                }
        }
    }
}
