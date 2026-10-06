<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Auth\CisAuthenticatorInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected CisAuthenticatorInterface $cisAuthenticator;

    public function __construct(CisAuthenticatorInterface $cisAuthenticator)
    {
        $this->cisAuthenticator = $cisAuthenticator;
    }

    /**
     * Menampilkan form login terpadu CIS / Admin.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->role === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('katalog.index');
        }

        return view('auth.login');
    }

    /**
     * Memproses autentikasi pengguna.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $throttleKey = Str::lower($credentials['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        $email = strtolower(trim($credentials['email']));
        $password = $credentials['password'];
        $adminEmails = config('perpustakaan.admin_emails', ['admin@del.ac.id']);

        // 1. Jalur Login Administrator (Wajib password lokal terenkripsi)
        if (in_array($email, $adminEmails, true)) {
            $user = User::where('email', $email)->first();

            // Jika admin belum ada di DB, buat akun admin default
            if (!$user) {
                $defaultPass = config('perpustakaan.admin_default_password', 'admin123');
                $user = User::create([
                    'name' => 'Administrator Perpustakaan',
                    'email' => $email,
                    'role' => 'admin',
                    'password' => Hash::make($defaultPass),
                ]);
            }

            if (!Hash::check($password, $user->password)) {
                RateLimiter::hit($throttleKey, 60);
                return back()->withErrors([
                    'email' => 'Kredensial yang dimasukkan tidak cocok dengan data kami.',
                ])->onlyInput('email');
            }

            RateLimiter::clear($throttleKey);
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Selamat datang kembali, ' . $user->name . '!');
        }

        // 2. Jalur Login Mahasiswa (Melalui CIS Mock / API)
        $authResult = $this->cisAuthenticator->authenticate($email, $password);

        if (!$authResult['success']) {
            RateLimiter::hit($throttleKey, 60);
            return back()->withErrors([
                'email' => $authResult['message'] ?? 'Kredensial akun CIS tidak valid.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($throttleKey);

        $user = User::where('email', $email)->first();
        if (!$user && !empty($authResult['nim'])) {
            $user = User::where('nim', $authResult['nim'])->first();
        }

        if (!$user) {
            $user = User::create([
                'name' => $authResult['name'] ?? 'Mahasiswa Del',
                'email' => $email,
                'nim' => $authResult['nim'] ?? null,
                'role' => 'mahasiswa',
                'password' => Hash::make($password),
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('katalog.index'))
            ->with('success', 'Selamat datang di SIPUS-Del, ' . $user->name . '!');
    }

    /**
     * Keluar dari sesi aplikasi.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
