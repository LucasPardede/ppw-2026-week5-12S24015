<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class Problem
{
    /**
     * Membentuk respons error standar RFC 7807 (application/problem+json)
     *
     * @param string $title
     * @param string $detail
     * @param int $status
     * @param string $slug
     * @param array $errors
     * @param string|null $instance
     * @return JsonResponse
     */
    public static function response(
        string $title,
        string $detail,
        int $status,
        string $slug = 'general-error',
        array $errors = [],
        ?string $instance = null
    ): JsonResponse {
        $payload = [
            'type' => url("/docs/errors/{$slug}"),
            'title' => $title,
            'status' => $status,
            'detail' => $detail,
            'instance' => $instance ?? request()->getRequestUri(),
        ];

        if (!empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()
            ->json($payload, $status)
            ->header('Content-Type', 'application/problem+json');
    }
}
