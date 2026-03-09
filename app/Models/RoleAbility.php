<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleAbility extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'ability',
        'type',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAllow()
    {
        return $this->type === 'allow';
    }

    public function isDeny()
    {
        return $this->type === 'deny';
    }
}
