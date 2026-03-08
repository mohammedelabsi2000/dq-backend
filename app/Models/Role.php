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

    public static function createWithAbilities(Request $request)
    {
        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->post('name'),
            ]);

            // foreach ($request->post('abilities') as $ability) {
            //     RoleAbility::create([
            //         'role_id' => $role->id,
            //         'ability_id' => $ability,
            //         'type' => 'allow',
            //     ]);
            // }
            // $allAbilities = array_keys(config('abilities'));
            if ($request->give_all) {
                $abilities = array_keys(config('abilities'));
            } else {
                $abilities = $request->abilities ?? [];
            }
            foreach ($abilities as $ability) {
                RoleAbility::create([
                    'role_id' => $role->id,
                    'ability' => $ability,
                    'type' => 'allow',
                ]);
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollBack();
            throw $e;
        }

        return $role;
    }

    public function updateWithAbilities(Request $request)
    {
        DB::beginTransaction();
        try {
            $this->update([
                'name' => $request->post('name'),
            ]);

            // foreach ($request->post('abilities') as $ability) {
            //     RoleAbility::updateOrCreate(
            //         [
            //             'role_id' => $this->id,
            //             'ability_id' => $ability,
            //         ],
            //         [
            //             'type' => 'allow',
            //         ]
            //     );
            // }
            // $allAbilities = array_keys(config('abilities'));
            if ($request->give_all) {
                $abilities = array_keys(config('abilities'));
            } else {
                $abilities = $request->abilities ?? [];
            }
            foreach ($abilities as $ability) {
                RoleAbility::updateOrCreate(
                    [
                        'role_id' => $this->id,
                        'ability' => $ability,
                    ],
                    [
                        'type' => 'allow',
                    ]
                );
            }
            Db::commit();
        } catch (\Exception $e) {
            Db::rollBack();
            throw $e;
        }


        return $this;
    }
}
