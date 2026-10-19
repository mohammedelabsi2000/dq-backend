<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * سجل ما يُرسل لبرنامج الجودة وما يصل منه، لتتبّع أي خلاف لاحقاً
 */
class QualitySyncLog extends Model
{
    public const OUTGOING = 'outgoing';
    public const INCOMING = 'incoming';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
    ];

    public function committee()
    {
        return $this->belongsTo(ExamCommittee::class, 'exam_committee_id');
    }
}
