<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccessTokensTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_login_with_email()
    {
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => Hash::make('123456'),
        ]);

        $response = $this->postJson('/api/auth/access-tokens', [
            'login' => 'test@test.com',
            'password' => '123456',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user',
                    'roles',
                    'permissions',
                    'scopes',
                ]
            ]);
    }

    /** @test */
    public function test_login_with_identity()
    {
        $user = User::factory()->create([
            'identity' => '123456',
            'password' => Hash::make('123456'),
        ]);

        $response = $this->postJson('/api/auth/access-tokens', [
            'login' => '123456',
            'password' => '123456',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function test_login_invalid_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('123456'),
        ]);

        $response = $this->postJson('/api/auth/access-tokens', [
            'login' => $user->email,
            'password' => 'wrong123',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_login_missing_fields()
    {
        $response = $this->postJson('/api/auth/access-tokens', []);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_logout_current_token()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/auth/access-tokens');

        $response->assertStatus(200);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id
        ]);
    }

    /** @test */
    public function test_logout_specific_token()
    {
        $user = User::factory()->create();

        $tokenObj = $user->createToken('test');
        $plain = $tokenObj->plainTextToken; // "4|kceMaWOb..."

        // نأخذ الجزء بعد | فقط — هذا ما يتوقعه findToken في الـ URL
        $tokenHash = explode('|', $plain)[1]; // "kceMaWOb..."

        $response = $this->withHeader('Authorization', "Bearer $plain")
            ->deleteJson("/api/auth/access-tokens/{$tokenHash}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenObj->accessToken->id,
        ]);
    }

    /** @test */
    public function test_logout_unauthenticated()
    {
        $response = $this->deleteJson('/api/auth/access-tokens');

        $response->assertStatus(401);
    }

    /** @test */
    public function test_change_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old123'),
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/change-password', [
                'old_password' => 'old123',
                'new_password' => 'new123',
                'new_password_confirmation' => 'new123',
            ]);

        $response->assertStatus(200);

        $this->assertTrue(
            Hash::check('new123', $user->fresh()->password)
        );
    }

    /** @test */
    public function test_change_password_wrong_old()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old123'),
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/change-password', [
                'old_password' => 'wrong',
                'new_password' => 'new123',
                'new_password_confirmation' => 'new123',
            ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function test_change_password_confirmation_mismatch()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old123'),
        ]);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/change-password', [
                'old_password' => 'old123',
                'new_password' => 'new123',
                'new_password_confirmation' => 'wrong',
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function test_login_response_includes_permissions_and_roles()
    {
        $user = User::factory()->create([
            'password' => Hash::make('123456'),
        ]);

        $response = $this->postJson('/api/auth/access-tokens', [
            'login' => $user->email,
            'password' => '123456',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'roles',
                    'permissions',
                    'scopes'
                ]
            ]);
    }
}
