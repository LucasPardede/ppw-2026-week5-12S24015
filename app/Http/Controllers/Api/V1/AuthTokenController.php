<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthTokenController extends Controller
{
    use ApiResponse;

    /**
     * POST /api/v1/auth/login
     * Memverifikasi kredensial pustakawan/admin & menerbitkan Stateless Bearer Token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $user = User::where('email', strtolower(trim($validated['email'])))->first();

        // Verifikasi keberadaan dan kecocokan password tanpa membocorkan eksistensi email
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->problemResponse(
                title: 'Invalid Credentials',
                detail: 'Kombinasi email atau password yang diberikan tidak cocok.',
                status: 401,
                errorSlug: 'invalid-credentials'
            );
        }

        // Keputusan Desain #3: Login API hanya untuk role admin
        if ($user->role !== 'admin') {
            return $this->problemResponse(
                title: 'Forbidden',
                detail: 'Akses ditolak: Hanya akun pustakawan/admin yang diizinkan masuk ke REST API.',
                status: 403,
                errorSlug: 'forbidden'
            );
        }

        $deviceName = $validated['device_name'] ?? 'sipus-api-client';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->successResponse([
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 'Otentikasi API berhasil. Lampirkan access_token pada header Authorization: Bearer <token>.');
    }

    /**
     * GET /api/v1/auth/me (auth:sanctum)
     * Mengambil profil pemilik token aktif.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'nim' => $user->nim,
        ], 'Profil pemilik token aktif berhasil diambil.');
    }

    /**
     * POST /api/v1/auth/logout (auth:sanctum)
     * Mencabut (revoke) token yang sedang dipakai saja.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(
            null,
            'Bearer Token berhasil dicabut (revoked). Sesi API diakhiri.'
        );
    }
}
