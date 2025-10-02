<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // カスタム例外のハンドリング
        if ($exception instanceof \App\Exceptions\DatabaseException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getUserMessage()
                ], 500);
            }
            return redirect()->back()->with('error', $exception->getUserMessage());
        }

        if ($exception instanceof \App\Exceptions\RecordingException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getUserMessage()
                ], 500);
            }
            return redirect()->back()->with('error', $exception->getUserMessage());
        }

        if ($exception instanceof \App\Exceptions\ExternalApiException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $exception->getUserMessage()
                ], 503);
            }
            return redirect()->back()->with('error', $exception->getUserMessage());
        }

        // ModelNotFoundExceptionのハンドリング
        if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '指定されたデータが見つかりません'
                ], 404);
            }
            abort(404, '指定されたデータが見つかりません');
        }

        // ValidationExceptionは既存の処理を使用
        return parent::render($request, $exception);
    }
}
