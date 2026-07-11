<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 各種セキュリティヘッダーを追加
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=()');

        // HTTPS通信時の場合にのみHSTSを付与する（ローカル開発環境での動作エラー防止）
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Content Security Policy (CSP) の設定
        // 本アプリケーションで使用している Google Fonts や Bootstrap / Mermaid JS の CDN を明示的に許可
        $csp = "default-src 'self'; "
             . "script-src 'self' https://cdn.jsdelivr.net; "
             . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; "
             . "font-src 'self' https://fonts.gstatic.com; "
             . "img-src 'self' data:; "
             . "connect-src 'self' https://cdn.jsdelivr.net; "
             . "frame-ancestors 'none';";
             
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
