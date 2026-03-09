<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function roleAbilities()
    {
        return $this->hasMany(RoleAbility::class);
    }

    // public static function createWithAbilities(string $name, array $abilities): self
    // {
    //     return DB::transaction(function () use ($name, $abilities) {
    //         $role = self::create(['name' => $name]);

    //         foreach ($abilities as $ability => $type) {
    //             RoleAbility::create([
    //                 'role_id' => $role->id,
    //                 'ability' => $ability,
    //                 'type'    => $type,
    //             ]);
    //         }

    //         return $role;
    //     });
    // }

    public static function createWithAbilities(array $data)
    {
        return DB::transaction(function () use ($data) {
            $role = self::create(['name' => $data['name']]);

            $abilities = $data['give_all'] ?? false
                ? array_keys(config('abilities'))
                : ($data['abilities'] ?? []);

            $role->insertAbilities($abilities);

            return $role;
        });
    }

    // public static function createWithAbilities(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $role = Role::create([
    //             'name' => $request->post('name'),
    //         ]);

    //         // foreach ($request->post('abilities') as $ability) {
    //         //     RoleAbility::create([
    //         //         'role_id' => $role->id,
    //         //         'ability_id' => $ability,
    //         //         'type' => 'allow',
    //         //     ]);
    //         // }
    //         // $allAbilities = array_keys(config('abilities'));
    //         if ($request->give_all) {
    //             $abilities = array_keys(config('abilities'));
    //         } else {
    //             $abilities = $request->abilities ?? [];
    //         }
    //         foreach ($abilities as $ability) {
    //             RoleAbility::create([
    //                 'role_id' => $role->id,
    //                 'ability' => $ability,
    //                 'type' => 'allow',
    //             ]);
    //         }
    //         Db::commit();
    //     } catch (\Exception $e) {
    //         Db::rollBack();
    //         throw $e;
    //     }

    //     return $role;
    // }

    // public function updateWithAbilities(string $name, array $abilities): self
    // {
    //     return DB::transaction(function () use ($name, $abilities) {
    //         $this->update(['name' => $name]);

    //         $this->roleAbilities()->delete();

    //         foreach ($abilities as $ability => $type) {
    //             RoleAbility::create([
    //                 'role_id' => $this->id,
    //                 'ability' => $ability,
    //                 'type'    => $type,
    //             ]);
    //         }

    //         return $this->load('roleAbilities');
    //     });
    // }

    public function updateWithAbilities(array $data)
    {
        return DB::transaction(function () use ($data) {
            $this->update(['name' => $data['name']]);

            $this->roleAbilities()->delete();

            $abilities = $data['give_all'] ?? false
                ? array_keys(config('abilities'))
                : ($data['abilities'] ?? []);

            $this->insertAbilities($abilities);

            return $this;
        });
    }

    public function deleteRole()
    {
        DB::transaction(function () {
            $this->roleAbilities()->delete();
            $this->delete();
        });
    }

    // public function updateWithAbilities(Request $request)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $this->update([
    //             'name' => $request->post('name'),
    //         ]);

    //         // foreach ($request->post('abilities') as $ability) {
    //         //     RoleAbility::updateOrCreate(
    //         //         [
    //         //             'role_id' => $this->id,
    //         //             'ability_id' => $ability,
    //         //         ],
    //         //         [
    //         //             'type' => 'allow',
    //         //         ]
    //         //     );
    //         // }
    //         // $allAbilities = array_keys(config('abilities'));
    //         if ($request->give_all) {
    //             $abilities = array_keys(config('abilities'));
    //         } else {
    //             $abilities = $request->abilities ?? [];
    //         }
    //         foreach ($abilities as $ability) {
    //             RoleAbility::updateOrCreate(
    //                 [
    //                     'role_id' => $this->id,
    //                     'ability' => $ability,
    //                 ],
    //                 [
    //                     'type' => 'allow',
    //                 ]
    //             );
    //         }
    //         Db::commit();
    //     } catch (\Exception $e) {
    //         Db::rollBack();
    //         throw $e;
    //     }


    //     return $this;
    // }

    private function insertAbilities(array $abilities)
    {
        $records = array_map(fn($ability) => [
            'role_id' => $this->id,
            'ability' => $ability,
            'type'    => 'allow',
        ], $abilities);

        RoleAbility::insert($records);
    }
}
