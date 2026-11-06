<?php

namespace App\Exceptions;

use RuntimeException;

class BatasPinjamTercapaiException extends RuntimeException
{
    public function __construct(string $message = 'Anda telah mencapai batas maksimal peminjaman buku aktif.')
    {
        parent::__construct($message);
    }
}
