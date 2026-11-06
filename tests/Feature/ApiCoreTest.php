<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ApiCoreTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $mahasiswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'role' => 'admin',
        ]);

        $this->mahasiswa = User::factory()->create([
            'name' => 'Lucas Pardede',
            'email' => 'lucas.pardede@del.ac.id',
            'nim' => '12S24015',
            'role' => 'mahasiswa',
        ]);

        // Route uji internal untuk memastikan middleware dan validasi
        Route::middleware(['api', 'auth:sanctum', 'api.admin'])->group(function () {
            Route::post('/api/test/admin-only', function () {
                request()->validate([
                    'title' => 'required|min:5',
                    'count' => 'required|integer|min:1',
                ]);
                return response()->json(['success' => true]);
            });
        });
    }

    public function test_api_404_endpoint_not_found_returns_rfc_7807(): void
    {
        $response = $this->getJson('/api/v1/non-existent-endpoint');

        $response->assertStatus(404);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJsonStructure([
            'type',
            'title',
            'status',
            'detail',
            'instance',
        ]);
        $response->assertJson([
            'status' => 404,
            'title' => 'Not Found',
            'instance' => '/api/v1/non-existent-endpoint',
        ]);
    }

    public function test_api_405_method_not_allowed_returns_rfc_7807(): void
    {
        // Rute /api/v1/auth/me hanya menerima GET
        $response = $this->postJson('/api/v1/auth/me');

        $response->assertStatus(405);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJsonStructure([
            'type',
            'title',
            'status',
            'detail',
            'instance',
        ]);
        $response->assertJson([
            'status' => 405,
            'title' => 'Method Not Allowed',
        ]);
    }

    public function test_api_401_unauthenticated_returns_rfc_7807(): void
    {
        // Rute /api/v1/auth/me membutuhkan Bearer token
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJsonStructure([
            'type',
            'title',
            'status',
            'detail',
            'instance',
        ]);
        $response->assertJson([
            'status' => 401,
            'title' => 'Unauthorized',
        ]);
    }

    public function test_api_403_non_admin_forbidden_returns_rfc_7807(): void
    {
        $response = $this->actingAs($this->mahasiswa, 'sanctum')
            ->postJson('/api/test/admin-only', [
                'title' => 'Testing Admin',
                'count' => 5,
            ]);

        $response->assertStatus(403);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJsonStructure([
            'type',
            'title',
            'status',
            'detail',
            'instance',
        ]);
        $response->assertJson([
            'status' => 403,
            'title' => 'Forbidden',
        ]);
    }

    public function test_api_422_validation_failed_returns_rfc_7807_with_column_errors(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/test/admin-only', []);

        $response->assertStatus(422);
        $response->assertHeader('Content-Type', 'application/problem+json');
        $response->assertJsonStructure([
            'type',
            'title',
            'status',
            'detail',
            'instance',
            'errors' => [
                'title',
                'count',
            ],
        ]);
        $response->assertJson([
            'status' => 422,
            'title' => 'Unprocessable Content',
        ]);
    }
}
