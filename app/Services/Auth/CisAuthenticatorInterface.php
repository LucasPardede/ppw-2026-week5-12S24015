<?php

namespace App\Services\Auth;

interface CisAuthenticatorInterface
{
    /**
     * Memvalidasi kredensial pengguna ke layanan CIS.
     *
     * @param string $email
     * @param string $password
     * @return array{success: bool, name?: string, nim?: string, message?: string}
     */
    public function authenticate(string $email, string $password): array;
}
