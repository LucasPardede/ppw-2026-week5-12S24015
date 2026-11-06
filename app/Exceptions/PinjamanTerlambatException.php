<?php

namespace App\Exceptions;

use RuntimeException;

class PinjamanTerlambatException extends RuntimeException
{
    public function __construct(string $message = 'Anda memiliki peminjaman buku yang telah melewati tanggal jatuh tempo.')
    {
        parent::__construct($message);
    }
}
