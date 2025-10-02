<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitLogin
{
    /**
     * ログイン試行のレート制限
     *
     * 5回失敗したら1分間ロックアウト
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'ログイン試行回数が多すぎます。' . $seconds . '秒後に再試行してください。',
                'retry_after' => $seconds
            ], 429);
        }

        RateLimiter::hit($key, 60); // 60秒間記録

        $response = $next($request);

        // ログイン成功時はカウンターをクリア
        if ($response->getStatusCode() === 302 && $request->user()) {
            RateLimiter::clear($key);
        }

        return $response;
    }

    /**
     * リクエストの署名を解決
     */
    protected function resolveRequestSignature(Request $request): string
    {
        return 'login-attempts:' . $request->ip() . ':' . ($request->input('email') ?? 'unknown');
    }
}
