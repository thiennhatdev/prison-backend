<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Prisoner;

class VerifyCustomerPhone
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $phone = $request->user()->phone;

        $exists = Prisoner::all()->contains(function ($prisoner) use ($request, $phone) {
            return collect($prisoner->phones)
                ->pluck('phone')
                ->contains($phone);
        });

        if (! $exists) {
            return response()->json([
                'message' => 'Số điện thoại không nằm trong danh sách đăng ký thăm',
                'status' => false,
            ], 403);
        }

        return $next($request);
    }
}
