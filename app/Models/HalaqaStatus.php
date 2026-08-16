<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HalaqaStatus extends Model
{
    use HasFactory, SoftDeletes;

    public static $usesAudit = true;

    protected $guarded = [];

    protected $casts = [
        'from_date' => 'date:Y-m-d',
        'to_date' => 'date:Y-m-d',
    ];

    public function halaqa()
    {
        return $this->belongsTo(Halaqa::class);
    }
    
    public function sponsorshipType()
    {
        return $this->belongsTo(Constant::class, 'sponsorship_type_id');
    }
}
