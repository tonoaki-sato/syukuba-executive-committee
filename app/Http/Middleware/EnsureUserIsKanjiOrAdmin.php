<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsKanjiOrAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isSystemAdmin() || $user->isKanji()) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'この操作を行う権限がありません。幹事または管理者のみ実行可能です。'], 403);
        }

        abort(403, 'このページにアクセスするには幹事またはシステム管理者権限が必要です。');
    }
}
