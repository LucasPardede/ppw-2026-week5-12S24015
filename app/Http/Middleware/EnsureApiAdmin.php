<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiProblemException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAdmin
{
    /**
     * Memastikan request API dilakukan oleh pengguna dengan peran admin.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     * @throws ApiProblemException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            throw new ApiProblemException(
                status: 403,
                title: 'Forbidden',
                detail: 'Akses ditolak: Operasi ini hanya diizinkan untuk peran admin/pustakawan.',
                slug: 'forbidden'
            );
        }

        return $next($request);
    }
}
