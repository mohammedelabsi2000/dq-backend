<?php
// app/Models/ApprovalLog.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'approval_request_id',
        'level',
        'action',
        'acted_by',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function approvalRequest()
    {
        return $this->belongsTo(ApprovalRequest::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
