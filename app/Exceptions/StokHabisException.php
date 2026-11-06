<?php

namespace App\Exceptions;

use RuntimeException;

class StokHabisException extends RuntimeException
{
    public function __construct(string $message = 'Stok buku ini sedang habis.')
    {
        parent::__construct($message);
    }
}
