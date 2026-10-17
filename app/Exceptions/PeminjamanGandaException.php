<?php

namespace App\Exceptions;

use RuntimeException;

class PeminjamanGandaException extends RuntimeException
{
    public function __construct(string $message = 'Anda sedang meminjam buku ini dan belum mengembalikannya.')
    {
        parent::__construct($message);
    }
}
