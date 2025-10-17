<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    /**
     * レスポンスを圧縮する
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 圧縮が無効の場合はスキップ
        if (!config('app.compress_response', true)) {
            return $response;
        }

        // クライアントがgzip圧縮をサポートしているか確認
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (strpos($acceptEncoding, 'gzip') === false) {
            return $response;
        }

        // 既に圧縮されている場合はスキップ
        if ($response->headers->has('Content-Encoding')) {
            return $response;
        }

        // レスポンスタイプが圧縮対象かチェック
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'text/html',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/json',
            'application/xml',
            'text/xml',
            'text/plain',
        ];

        $shouldCompress = false;
        foreach ($compressibleTypes as $type) {
            if (strpos($contentType, $type) !== false) {
                $shouldCompress = true;
                break;
            }
        }

        if (!$shouldCompress) {
            return $response;
        }

        // レスポンスの内容を取得
        $content = $response->getContent();

        // 小さなレスポンスは圧縮しない（オーバーヘッドが大きいため）
        $minSize = config('app.compress_min_size', 1024); // デフォルト1KB
        if (strlen($content) < $minSize) {
            return $response;
        }

        // gzip圧縮を実行
        $compressedContent = gzencode($content, 6); // 圧縮レベル6（バランス型）

        if ($compressedContent !== false) {
            $response->setContent($compressedContent);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('Content-Length', strlen($compressedContent));

            // Vary ヘッダーを追加（キャッシュの互換性向上）
            $response->headers->set('Vary', 'Accept-Encoding', false);
        }

        return $response;
    }
}
