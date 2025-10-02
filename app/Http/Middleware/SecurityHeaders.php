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

        // X-Frame-Options: クリックジャッキング攻撃を防ぐ
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-Content-Type-Options: MIMEタイプスニッフィングを防ぐ
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-XSS-Protection: XSS攻撃を防ぐ（古いブラウザ用）
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy: リファラー情報の制御
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions-Policy: 機能ポリシーの設定
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Content-Security-Policy: XSS攻撃を防ぐ
        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com",
            "img-src 'self' data: https: http:",
            "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com",
            "connect-src 'self' http://radiko.jp https://radiko.jp",
            "frame-ancestors 'self'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // Strict-Transport-Security: HTTPS接続を強制（本番環境のみ）
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
