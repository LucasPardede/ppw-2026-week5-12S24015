<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiCisAuthenticator implements CisAuthenticatorInterface
{
    public function authenticate(string $email, string $password): array
    {
        $apiUrl = config('perpustakaan.cis_api_url', env('CIS_API_URL', ''));

        if (empty($apiUrl)) {
            // Fallback ke simulasi jika endpoint API belum dikonfigurasi
            return (new MockCisAuthenticator())->authenticate($email, $password);
        }

        try {
            $response = Http::timeout(10)->post($apiUrl, [
                'email' => $email,
                'password' => $password,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'name' => $data['name'] ?? explode('@', $email)[0],
                    'nim' => $data['nim'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => 'Kredensial CIS tidak valid.',
            ];
        } catch (\Throwable $e) {
            Log::error('Gagal menghubungi API CIS: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Layanan CIS eksternal sedang tidak dapat dihubungi.',
            ];
        }
    }
}
