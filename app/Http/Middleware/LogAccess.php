<?php

namespace App\Http\Middleware;

use App\Models\AccessLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('access_log_started_at', microtime(true));

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        // Termination receives the final response, including rendered exceptions.
        $startedAt = $request->attributes->get('access_log_started_at');
        if ($startedAt === null) {
            return;
        }

        try {
            $isAdmin = $request->is('admin', 'admin/*');
            $user = $isAdmin ? auth('twill_users')->user() : $request->user();

            AccessLog::create([
                'ip' => $request->ip(),
                'accessed_at' => now(),
                'method' => substr($request->method(), 0, 20),
                // Exclude query strings, request bodies, cookies and authorization headers.
                // Verification URLs contain bearer tokens in their path.
                'path' => mb_substr(preg_replace('#(/visitation-schedule/verify/)[^/]+#', '$1[redacted]', $request->getPathInfo()), 0, 4096),
                'status' => $response->getStatusCode(),
                'duration_ms' => min(4294967295, max(0, (int) round((microtime(true) - $startedAt) * 1000))),
                'user_agent' => mb_substr($request->userAgent() ?? '', 0, 2048),
                'account_type' => $user ? ($isAdmin ? 'admin' : 'customer') : null,
                'account_id' => $user ? (string) $user->getAuthIdentifier() : null,
                'account_name' => $user ? mb_substr($user->name ?? $user->email ?? '', 0, 255) : null,
            ]);
        } catch (Throwable $exception) {
            // A logging failure must not turn a successful request into a failure.
            Log::warning('Unable to persist access log', ['exception' => get_class($exception)]);
        }
    }
}
