<?php

namespace App\Exceptions;

use RuntimeException;

class NimTidakTersediaException extends RuntimeException
{
    public function __construct(string $message = 'Data NIM tidak ditemukan pada profil akun Anda. Peminjaman memerlukan NIM yang valid.')
    {
        parent::__construct($message);
    }
}
