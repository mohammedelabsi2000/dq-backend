<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HalaqaStatus extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function halaqa()
    {
        return $this->belongsTo(Halaqa::class);
    }
    
    public function statusType()
    {
        return $this->belongsTo(Constant::class, 'status_type_id');
    }
    
    public function sponsorshipType()
    {
        return $this->belongsTo(Constant::class, 'sponsorship_type_id');
    }
}
