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

    public static function createWithAbilities(array $data)
    {
        return DB::transaction(function () use ($data) {
            $role = self::create(['name' => $data['name']]);

            $abilities = $data['give_all'] ?? false
                ? collect(config('abilities'))->flatten(1)->pluck('ability')->toArray()
                : ($data['abilities'] ?? []);

            $role->insertAbilities($abilities);

            return $role;
        });
    }

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
