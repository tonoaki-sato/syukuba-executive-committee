<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            // 対象年セッションの範囲チェック＆補正（2026年より前、または将来の年は不可）
            $currentYear = (int)date('Y');
            $activeYear = (int)session('active_fiscal_year', $currentYear);
            if ($activeYear < 2026 || $activeYear > $currentYear) {
                session(['active_fiscal_year' => $currentYear]);
            }

            $user = Auth::user();

            if ($user->status === 'temporary') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'error' => 'アカウントはシステム管理者の承認待ちです。承認されるまでこの機能は利用できません。'
                    ], 403);
                }

                // 承認待ち画面自体へのアクセスは許可
                if ($request->routeIs('register.pending') || $request->routeIs('logout')) {
                    return $next($request);
                }

                return redirect()->route('register.pending');
            }

            // 無効アカウントの強制ログアウト
            if (in_array($user->status, ['suspended', 'expelled', 'rejected'])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors([
                    'email' => 'このアカウントは現在ご利用いただけません。',
                ]);
            }

            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'ログインが必要です。'], 401);
        }

        return redirect()->route('login');
    }
}
