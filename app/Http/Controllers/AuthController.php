<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Auth\CisAuthenticatorInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected CisAuthenticatorInterface $cisAuthenticator
    ) {}

    /**
     * Menampilkan form login terpadu CIS / Admin.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->role === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('katalog.index');
        }

        // Ambil email terakhir dari cookie jika tersedia (FITUR 2)
        $rememberedEmail = $request->cookie('del_last_user_email', '');

        return view('auth.login', compact('rememberedEmail'));
    }

    /**
     * Memproses autentikasi pengguna dengan session regeneration dan proteksi cookie.
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

        $email = strtolower(trim($credentials['email']));
        $password = $credentials['password'];
        $remember = $request->boolean('remember');
        $rememberEmail = $request->boolean('remember_email');

        $throttleKey = Str::lower($email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        // Cek apakah akun sudah terdaftar di database (berdasarkan email atau NIM dari CIS mock)
        $user = User::where('email', $email)->first();

        // Jalankan CIS Mock jika akun belum ditemukan atau mahasiswa
        $authResult = $this->cisAuthenticator->authenticate($email, $password);

        if (!$user && $authResult['success'] && !empty($authResult['nim'])) {
            $user = User::where('nim', $authResult['nim'])->first();
        }

        // Pendaftaran otomatis HANYA jika akun benar-benar belum ada di tabel users
        // Penutupan celah: TIDAK PERNAH membuat akun admin secara otomatis
        if (!$user && $authResult['success']) {
            $user = User::create([
                'name' => $authResult['name'] ?? 'Mahasiswa Del',
                'email' => $email,
                'nim' => $authResult['nim'] ?? null,
                'role' => 'mahasiswa', // Wajib mahasiswa, admin hanya dari seeder
                'password' => Hash::make($password),
            ]);
        }

        // Sinkronisasi kata sandi untuk simulasi CIS mahasiswa jika akun cocok
        if ($user && $user->role === 'mahasiswa' && $authResult['success']) {
            $user->password = Hash::make($password);
            $user->save();
        }

        $loginEmail = $user ? $user->email : $email;

        // Otentikasi formal menggunakan Auth::attempt()
        if (!Auth::attempt(['email' => $loginEmail, 'password' => $password], $remember)) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'email' => 'Kredensial yang dimasukkan tidak cocok dengan data kami.',
            ])->onlyInput('email');
        }

        // Berhasil autentikasi: bersihkan rate limiter
        RateLimiter::clear($throttleKey);

        // Regenerasi Session ID untuk mencegah Session Fixation Attack
        $request->session()->regenerate();

        // State Session tambahan: login_timestamp (ditampilkan di navbar)
        $request->session()->put('login_timestamp', now()->format('d M Y, H:i'));

        // FITUR 2: Cookie del_last_user_email (10080 menit / 7 hari)
        // Disimpan HANYA bila kotak "Ingat email saya" atau "remember" dicentang
        // Menggunakan HttpOnly=true dan SameSite='lax'
        if ($rememberEmail || $remember) {
            Cookie::queue(
                'del_last_user_email',
                $email,
                10080, // 7 hari (menit)
                '/',
                null,
                false, // secure=false pada localhost/http
                true,  // httpOnly
                false,
                'lax'  // sameSite
            );
        } else {
            Cookie::queue(Cookie::forget('del_last_user_email'));
        }

        $authenticatedUser = Auth::user();
        $targetRoute = $authenticatedUser->role === 'admin'
            ? route('admin.dashboard')
            : route('katalog.index');

        return redirect()->intended($targetRoute)
            ->with('success', 'Selamat datang kembali, ' . $authenticatedUser->name . '!');
    }

    /**
     * Keluar dari sistem: Auth::logout(), invalidate session, dan regenerateToken.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}
