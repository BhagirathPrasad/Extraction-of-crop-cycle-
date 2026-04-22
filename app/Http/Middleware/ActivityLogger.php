<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogger
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log authenticated state-changing requests
        if (auth()->check() && !$request->isMethod('GET')) {
            try {
                ActivityLog::create([
                    'user_id'    => auth()->id(),
                    'action'     => $this->resolveAction($request),
                    'description'=> 'Web request: ' . $request->method() . ' ' . $request->path(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'url'        => $request->fullUrl(),
                    'method'     => $request->method(),
                ]);
            } catch (\Throwable) {
                // Silently fail — never block user request due to logging
            }
        }

        return $response;
    }

    private function resolveAction(Request $request): string
    {
        return match ($request->method()) {
            'POST'   => 'created',
            'PUT', 'PATCH' => 'updated',
            'DELETE' => 'deleted',
            default  => 'accessed',
        };
    }
}
