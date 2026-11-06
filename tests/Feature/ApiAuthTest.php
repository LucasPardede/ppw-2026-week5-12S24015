<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    private User $pustakawan;
    private User $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pustakawan = User::create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        $this->mahasiswa = User::create([
            'name' => 'Lucas Pardede',
            'email' => 'lucas.pardede@del.ac.id',
            'nim' => '12S24015',
            'role' => 'mahasiswa',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_login_successful_returns_bearer_token_and_user_minimal(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'password123',
            'device_name' => 'postman-unit-test',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token_type',
                'access_token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                ],
            ],
        ]);

        $data = $response->json('data');
        $this->assertEquals('Bearer', $data['token_type']);
        $this->assertNotEmpty($data['access_token']);
        $this->assertEquals('pustakawan@del.ac.id', $data['user']['email']);
        $this->assertEquals('admin', $data['user']['role']);
        $this->assertArrayNotHasKey('password', $data['user'], 'Password hash tidak boleh bocor di respons API.');
    }

    public function test_login_with_wrong_password_returns_401_problem(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 401,
            'title' => 'Invalid Credentials',
            'detail' => 'Kombinasi email atau password yang diberikan tidak cocok.',
        ]);
    }

    public function test_login_with_non_existent_email_returns_401_problem(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'tidak.ada@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 401,
            'title' => 'Invalid Credentials',
            'detail' => 'Kombinasi email atau password yang diberikan tidak cocok.',
        ]);
    }

    public function test_login_with_student_role_returns_403_problem(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'lucas.pardede@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 403,
            'title' => 'Forbidden',
        ]);
    }

    public function test_me_without_token_returns_401_problem(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 401,
            'title' => 'Unauthorized',
        ]);
    }

    public function test_me_with_valid_token_returns_authenticated_user_profile(): void
    {
        $token = $this->pustakawan->createToken('test-device')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $this->pustakawan->id,
                'name' => $this->pustakawan->name,
                'email' => $this->pustakawan->email,
                'role' => 'admin',
            ],
        ]);
    }

    public function test_logout_revokes_current_token_and_subsequent_request_fails_with_401(): void
    {
        $token = $this->pustakawan->createToken('token-to-revoke')->plainTextToken;

        $logoutResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200);
        $logoutResponse->assertJson([
            'success' => true,
            'message' => 'Bearer Token berhasil dicabut (revoked). Sesi API diakhiri.',
        ]);

        // Reset guard state antara dua HTTP call di feature test
        $this->app['auth']->forgetGuards();

        // Coba gunakan token yang sama kembali -> harus 401
        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $meResponse->assertStatus(401);
    }

    public function test_logout_only_revokes_current_token_other_tokens_remain_valid(): void
    {
        $token1 = $this->pustakawan->createToken('device-1')->plainTextToken;
        $token2 = $this->pustakawan->createToken('device-2')->plainTextToken;

        // Revoke token1
        $this->withHeader('Authorization', "Bearer {$token1}")
            ->postJson('/api/v1/auth/logout')
            ->assertStatus(200);

        // Reset guard state antara dua HTTP call
        $this->app['auth']->forgetGuards();

        // Token1 sudah mati
        $this->withHeader('Authorization', "Bearer {$token1}")
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);

        // Reset guard state
        $this->app['auth']->forgetGuards();

        // Token2 tetap aktif!
        $this->withHeader('Authorization', "Bearer {$token2}")
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200);
    }

    public function test_login_rate_limiting_triggers_429_after_threshold(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'wrong@del.ac.id',
                'password' => 'wrong',
            ]);
        }

        // Panggilan ke-6 harus kena rate limit 429
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@del.ac.id',
            'password' => 'wrong',
        ]);

        $response->assertStatus(429);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJson([
            'status' => 429,
            'title' => 'Too Many Requests',
        ]);
    }
}
