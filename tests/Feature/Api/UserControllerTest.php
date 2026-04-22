<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Mosque;
use App\Models\Constant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\Traits\SeedsTestData;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, SeedsTestData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    /** @test */
    public function test_index_returns_users_with_pagination()
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/users');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'fName',
                        'sName',
                        'thName',
                        'family',
                        'full_name',
                        'dob',
                        'gender',
                        'genderText',
                        'numChildren',
                        'identity',
                        'phone',
                        'whatsapp',
                        'email',
                        'jobname',
                        'job_place',
                        'job_salary',
                        'mosque',
                        'marital_status',
                        'prefix_name',
                        'roles',
                        'abilities',
                        'user_scopes',
                        'location'
                    ]
                ],
                'total',
                'skip',
                'limit',
            ]);

        $this->assertEquals(true, $response->json('success'));
        $this->assertArrayHasKey('total', $response->json());
    }

    /** @test */
    public function test_index_requires_authentication()
    {
        $response = $this->getJson('/api/users');

        $response->assertUnauthorized();
    }

    /** @test */
    public function test_index_requires_authorization()
    {
        $regularUser = User::factory()->create();

        $response = $this->actingAs($regularUser, 'sanctum')
            ->getJson('/api/users');

        $response->assertForbidden();
    }

    /** @test */
    public function test_index_with_search_filter()
    {
        User::factory()->create(['fName' => 'Mohammed']);
        User::factory()->create(['fName' => 'Ahmed']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/users?search=Mohammed');

        $response->assertOk();
        $this->assertStringContainsString('Mohammed', $response->json('data.0.fName'));
    }

    /** @test */
    public function test_index_with_pagination_parameters()
    {
        User::factory()->count(10)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/users?skip=2&limit=3');

        $response->assertOk();
        $this->assertEquals(2, $response->json('skip'));
        $this->assertEquals(3, $response->json('limit'));
    }

    /** @test */
    public function test_store_creates_user_successfully()
    {
        $data = User::factory()->make()->toArray();
        $data['password'] = 'password123';

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/users', $data);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'identity',
                    'fName',
                    'sName',
                    'thName',
                    'family',
                    'email',
                    'phone',
                    'gender'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'identity' => $data['identity'],
            'fName' => $data['fName'],
            'email' => $data['email']
        ]);
    }

    /** @test */
    public function test_store_validates_required_fields()
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/users', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
                'password',
                'dob'
            ]);
    }

    /** @test */
    public function test_store_rejects_duplicate_identity()
    {
        $identity = '123456789';

        User::where('identity', $identity)->first()
            ?? User::factory()->create([
                'identity' => $identity,
            ]);

        $data = User::factory()->make(['identity' => $identity])->toArray();
        $data['password'] = 'password123';

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/users', $data);

        $response->assertUnprocessable()
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function test_store_hashes_password()
    {
        $data = User::factory()->make()->toArray();
        $data['password'] = 'secret123';

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/users', $data);

        $response->assertCreated();

        $user = User::where('identity', $data['identity'])->first();
        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertNotEquals('secret123', $user->password);
    }

    /** @test */
    /* public function test_store_restores_soft_deleted_user()
    {
        $user = User::factory()->create();
        $user->delete();

        $data = User::factory()->make(['identity' => $user->identity])->toArray();
        $data['password'] = 'password123';

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/users', $data);

        $response->assertCreated();

        $restoredUser = User::find($user->id);
        $this->assertNotNull($restoredUser);
        $this->assertFalse($restoredUser->trashed());
    } */

    /** @test */
    public function test_show_returns_user_details()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'identity',
                    'fName',
                    'sName',
                    'thName',
                    'family',
                    'email',
                    'phone',
                    'gender',
                    'mosque',
                    'marital_status',
                    'prefix_name',
                    'roles' => [
                        '*' => [
                            'name',
                            'permissions'
                        ]
                    ]
                ]
            ]);

        $this->assertEquals($user->id, $response->json('data.id'));
    }

    /** @test */
    public function test_show_requires_authentication()
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/users/{$user->id}");

        $response->assertUnauthorized();
    }

    /** @test */
    public function test_show_requires_authorization()
    {
        $regularUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($regularUser, 'sanctum')
            ->getJson("/api/users/{$targetUser->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function test_update_modifies_user_successfully()
    {
        $user = User::factory()->create([
            'fName' => 'Old Name',
            'email' => 'old@example.com'
        ]);

        $updateData = [
            'fName' => 'New Name',
            'email' => 'new@example.com',
            'phone' => '0555555555'
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/users/{$user->id}", $updateData);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'fName' => 'New Name',
            'email' => 'new@example.com'
        ]);
    }

    /** @test */
    public function test_update_hashes_password_when_provided()
    {
        $user = User::factory()->create(['password' => bcrypt('oldpassword')]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/users/{$user->id}", [
                'password' => 'newpassword123'
            ]);

        $response->assertOk();

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertFalse(Hash::check('oldpassword', $user->password));
    }

    /** @test */
    public function test_update_does_not_change_password_when_not_provided()
    {
        $oldPassword = bcrypt('oldpassword');
        $user = User::factory()->create(['password' => $oldPassword]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/users/{$user->id}", [
                'fName' => 'New Name'
            ]);

        $response->assertOk();

        $user->refresh();
        $this->assertEquals($oldPassword, $user->password);
    }

    /** @test */
    public function test_update_requires_authentication()
    {
        $user = User::factory()->create();

        $response = $this->putJson("/api/users/{$user->id}", ['fName' => 'Test']);

        $response->assertUnauthorized();
    }

    /** @test */
    public function test_update_requires_authorization()
    {
        $regularUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($regularUser, 'sanctum')
            ->putJson("/api/users/{$targetUser->id}", ['fName' => 'Test']);

        $response->assertForbidden();
    }

    /** @test */
    public function test_destroy_soft_deletes_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertNull(User::find($user->id));
        $this->assertNotNull(User::withTrashed()->find($user->id));
    }

    /** @test */
    public function test_destroy_requires_authentication()
    {
        $user = User::factory()->create();

        $response = $this->deleteJson("/api/users/{$user->id}");

        $response->assertUnauthorized();
    }

    /** @test */
    public function test_destroy_requires_authorization()
    {
        $regularUser = User::factory()->create();
        $targetUser = User::factory()->create();

        $response = $this->actingAs($regularUser, 'sanctum')
            ->deleteJson("/api/users/{$targetUser->id}");

        $response->assertForbidden();
    }

    /** @test */
    public function test_user_resource_includes_relationships()
    {
        $mosque = Mosque::factory()->create();
        $maritalStatus = Constant::factory()->create(['constant_type_id' => 1]);
        $prefix = Constant::factory()->create(['constant_type_id' => 2]);

        $user = User::factory()->create([
            'mosque_id' => $mosque->id,
            'marital_status_id' => $maritalStatus->id,
            'prefix_name_id' => $prefix->id
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/users/{$user->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'mosque' => ['id', 'name'],
                    'marital_status' => ['id', 'name'],
                    'prefix_name' => ['id', 'name'],
                    'roles'
                ]
            ]);
    }

    /** @test */
    public function test_index_includes_all_required_relationships()
    {
        User::factory()->count(3)->create();

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/users');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'roles'
                    ]
                ]
            ]);

        foreach ($response->json('data') as $item) {
            $this->assertNullableRelation($item, 'mosque', ['id', 'name']);
            $this->assertNullableRelation($item, 'mosque.region', ['id', 'name']);
            $this->assertNullableRelation($item, 'mosque.region.branch', ['id', 'name']);
            $this->assertNullableRelation($item, 'marital_status', ['id', 'name']);
            $this->assertNullableRelation($item, 'prefix_name', ['id', 'name']);
        }
    }

    /**
     * Helper method to assert that a relationship field is either null or has the expected structure
     * 
     * @param array $item
     * @param string $path
     * @param array $fields
     * @return void
     */
    protected function assertNullableRelation(array $item, string $path, array $fields): void
    {
        $value = data_get($item, $path);

        if ($value !== null) {
            foreach ($fields as $field) {
                $this->assertArrayHasKey($field, $value, "Relation {$path} should have field {$field}");
            }
        }
    }
}
