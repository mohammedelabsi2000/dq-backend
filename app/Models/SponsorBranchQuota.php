<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SponsorBranchQuota extends Model
{
    use HasFactory, SoftDeletes;

    public static $usesAudit = true;

    protected $fillable = [
        'sponsor_id',
        'branch_id',
        'gender',
        'quota',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'gender' => Gender::class,
    ];

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
