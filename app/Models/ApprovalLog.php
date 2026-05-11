<?php

namespace App\Models;

use App\Enums\ApprovalLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'approval_request_id',
        'level',
        'action',
        'acted_by',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'level'      => ApprovalLevel::class,
        'created_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    public function request()
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }
}
