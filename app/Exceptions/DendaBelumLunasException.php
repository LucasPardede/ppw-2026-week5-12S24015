<?php

namespace App\Exceptions;

use RuntimeException;

class DendaBelumLunasException extends RuntimeException
{
    public function __construct(string $message = 'Anda memiliki tunggakan denda keterlambatan yang belum lunas.')
    {
        parent::__construct($message);
    }
}
