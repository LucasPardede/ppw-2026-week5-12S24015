<?php

namespace App\Services\Auth;

class MockCisAuthenticator implements CisAuthenticatorInterface
{
    /**
     * Otentikasi simulasi CIS:
     * Format email valid dan password terisi diterima.
     * Nama diturunkan dari email, NIM diekstrak jika ada pola NIM.
     */
    public function authenticate(string $email, string $password): array
    {
        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Email dan kata sandi wajib diisi.',
            ];
        }

        $localPart = explode('@', $email)[0];
        
        // Deteksi pola NIM Del (misal 12S24015 atau format teks nama)
        $nim = null;
        if (preg_match('/^[0-9]{2}[A-Za-z]{1,3}[0-9]{3,5}$/', $localPart)) {
            $nim = strtoupper($localPart);
            $formattedName = 'Mahasiswa ' . $nim;
        } else {
            $words = explode('.', str_replace(['_', '-'], '.', $localPart));
            $formattedName = ucwords(implode(' ', $words));
        }

        return [
            'success' => true,
            'name' => $formattedName,
            'nim' => $nim,
        ];
    }
}
