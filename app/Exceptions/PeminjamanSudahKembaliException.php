<?php

namespace App\Exceptions;

use RuntimeException;

class PeminjamanSudahKembaliException extends RuntimeException
{
    public function __construct(string $message = 'Peminjaman ini sudah tercatat dikembalikan sebelumnya.')
    {
        parent::__construct($message);
    }
}
