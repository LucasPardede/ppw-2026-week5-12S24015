<?php

use App\Exceptions\ApiProblemException;
use App\Support\Problem;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));

        $middleware->validateCsrfTokens(except: [
            'soap/*',
        ]);

        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'api.admin' => \App\Http\Middleware\EnsureApiAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // 1. Paksa setiap rute berawalan /api/* merender respons JSON
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // 2. Render ApiProblemException (dilempar dari transaksi atau controller)
        $exceptions->render(function (ApiProblemException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $e->render($request);
            }
        });

        // 3. Error Validasi Input (422 Unprocessable Content - RFC 7807)
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return Problem::response(
                    title: 'Unprocessable Content',
                    detail: 'Satu atau lebih atribut input yang dikirim tidak memenuhi aturan validasi.',
                    status: 422,
                    slug: 'validation-failed',
                    errors: $e->errors(),
                    instance: $request->getRequestUri()
                );
            }
        });

        // 4. Error Belum Login / Token Tidak Valid (401 Unauthorized - RFC 7807)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return Problem::response(
                    title: 'Unauthorized',
                    detail: 'Akses ditolak. Sertakan Bearer Token yang valid pada header Authorization.',
                    status: 401,
                    slug: 'unauthenticated',
                    instance: $request->getRequestUri()
                );
            }
        });

        // 5. Error Akses Ditolak / Forbidden (403 Forbidden - RFC 7807)
        $exceptions->render(function (AccessDeniedHttpException|AuthorizationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return Problem::response(
                    title: 'Forbidden',
                    detail: $e->getMessage() ?: 'Akses ditolak: Anda tidak memiliki izin untuk mengakses resource ini.',
                    status: 403,
                    slug: 'forbidden',
                    instance: $request->getRequestUri()
                );
            }
        });

        // 6. Error Endpoint Tidak Ditemukan (404 Not Found - RFC 7807)
        $exceptions->render(function (NotFoundHttpException|ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return Problem::response(
                    title: 'Not Found',
                    detail: 'Alamat URI endpoint atau resource yang diminta tidak ditemukan.',
                    status: 404,
                    slug: 'not-found',
                    instance: $request->getRequestUri()
                );
            }
        });

        // 7. Error Metode HTTP Tidak Diizinkan (405 Method Not Allowed - RFC 7807)
        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return Problem::response(
                    title: 'Method Not Allowed',
                    detail: "Metode HTTP '{$request->method()}' tidak diizinkan untuk endpoint ini.",
                    status: 405,
                    slug: 'method-not-allowed',
                    instance: $request->getRequestUri()
                );
            }
        });

        // 8. Error Batas Panggilan / Rate Limit (429 Too Many Requests - RFC 7807)
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return Problem::response(
                    title: 'Too Many Requests',
                    detail: 'Terlalu banyak permintaan dalam waktu singkat. Silakan tunggu beberapa saat.',
                    status: 429,
                    slug: 'too-many-requests',
                    instance: $request->getRequestUri()
                );
            }
        });

        // 9. Fallback Error Server Internal (500 Internal Server Error - RFC 7807)
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $isDebug = config('app.debug', false);
                return Problem::response(
                    title: 'Internal Server Error',
                    detail: $isDebug ? $e->getMessage() : 'Terjadi kesalahan internal pada server.',
                    status: 500,
                    slug: 'internal-server-error',
                    instance: $request->getRequestUri()
                );
            }
        });
    })->create();
