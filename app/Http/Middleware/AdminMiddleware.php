<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Yêu cầu request đã được authenticate (thường đi kèm auth:sanctum).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập',
            ], 401);
        }

        if (($user->quyen ?? null) !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập',
            ], 403);
        }

        return $next($request);
    }
}
