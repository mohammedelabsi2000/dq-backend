<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HalaqaSponsorship extends Model
{
    use HasFactory, SoftDeletes;

    public static $usesAudit = true;

    protected $fillable = [
        'halaqa_id',
        'sponsor_id',
        'from_date',
        'to_date',
        'stop_reason',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'from_date' => 'date:Y-m-d',
        'to_date' => 'date:Y-m-d',
    ];

    public function halaqa()
    {
        return $this->belongsTo(Halaqa::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->whereNull('to_date');
    }

    public function stop(?string $reason = null): void
    {
        $this->to_date = $this->to_date ?? now()->toDateString();
        $this->stop_reason = $reason;
        $this->save();
    }
}
