<?php

namespace Tests\Feature\Auth;

use App\Listeners\InvalidateIdleAccessToken;
use App\Models\User;
use Tests\TestCase;

class CheckTokenIdleTimeoutTest extends TestCase
{
    /** @test */
    public function test_request_within_idle_window_is_allowed()
    {
        $user = User::factory()->create();

        $tokenObj = $user->createToken('idle-test');
        $tokenObj->accessToken->forceFill([
            'last_used_at' => now()->subSeconds(5),
        ])->save();

        $response = $this->withHeader('Authorization', "Bearer {$tokenObj->plainTextToken}")
            ->getJson('/api/user');

        $response->assertStatus(200);

        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $tokenObj->accessToken->id,
        ]);
    }

    /** @test */
    public function test_request_after_idle_window_is_rejected_and_token_deleted()
    {
        $user = User::factory()->create();

        $tokenObj = $user->createToken('idle-test');
        $tokenObj->accessToken->forceFill([
            'last_used_at' => now()->subMinutes(InvalidateIdleAccessToken::IDLE_TIMEOUT_MINUTES + 1),
        ])->save();

        $response = $this->withHeader('Authorization', "Bearer {$tokenObj->plainTextToken}")
            ->getJson('/api/user');

        $response->assertStatus(401)
            ->assertJson(['message' => 'Session expired.']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $tokenObj->accessToken->id,
        ]);
    }

    /** @test */
    public function test_request_with_never_used_token_is_allowed()
    {
        $user = User::factory()->create();

        $tokenObj = $user->createToken('idle-test');
        // Sanctum leaves last_used_at null until the token is first used.
        $this->assertNull($tokenObj->accessToken->last_used_at);

        $response = $this->withHeader('Authorization', "Bearer {$tokenObj->plainTextToken}")
            ->getJson('/api/user');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_request_without_token_passes_through_to_auth_guard()
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    /** @test */
    public function test_second_request_after_a_deleted_idle_token_still_fails_auth()
    {
        $user = User::factory()->create();

        $tokenObj = $user->createToken('idle-test');
        $tokenObj->accessToken->forceFill([
            'last_used_at' => now()->subMinutes(InvalidateIdleAccessToken::IDLE_TIMEOUT_MINUTES + 1),
        ])->save();

        $this->withHeader('Authorization', "Bearer {$tokenObj->plainTextToken}")
            ->getJson('/api/user')
            ->assertStatus(401);

        // Same (now-deleted) token should no longer authenticate at all.
        $this->withHeader('Authorization', "Bearer {$tokenObj->plainTextToken}")
            ->getJson('/api/user')
            ->assertStatus(401);
    }
}
