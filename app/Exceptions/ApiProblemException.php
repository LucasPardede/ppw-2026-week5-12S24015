<?php

namespace App\Exceptions;

use App\Support\Problem;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiProblemException extends Exception
{
    protected int $status;
    protected string $title;
    protected string $detail;
    protected string $slug;
    protected array $errors;

    public function __construct(
        int $status,
        string $title,
        string $detail,
        string $slug = 'general-error',
        array $errors = []
    ) {
        parent::__construct($detail, $status);
        $this->status = $status;
        $this->title = $title;
        $this->detail = $detail;
        $this->slug = $slug;
        $this->errors = $errors;
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function render(Request $request): JsonResponse
    {
        return Problem::response(
            title: $this->title,
            detail: $this->detail,
            status: $this->status,
            slug: $this->slug,
            errors: $this->errors,
            instance: $request->getRequestUri()
        );
    }
}
