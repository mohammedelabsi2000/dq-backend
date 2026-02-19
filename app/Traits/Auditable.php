<?php
namespace App\Traits;

use App\Models\Audit;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->audit('created');
        });

        static::updated(function ($model) {
            $model->audit('updated');
        });

        static::deleted(function ($model) {
            $model->audit('deleted');
        });

        static::restored(function ($model) {
            $model->audit('restored');
        });
    }

    protected function audit($event)
    {
        Audit::create([
            'user_id' => auth()->check() ? auth()->id() : null,
            'event' => $event,
            'auditable_id' => $this->id,
            'auditable_type' => get_class($this),
            'old_values' => $this->castArrayForAudit($this->getOriginal()),
            'new_values' => $this->castArrayForAudit($this->getAttributes()),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }


    protected function castArrayForAudit(array $values): ?string
    {
        // نحذف أي قيمة غير قابلة للتخزين في JSON
        $filtered = array_filter($values, function ($value) {
            return !is_object($value) && !is_resource($value);
        });

        return !empty($filtered) ? json_encode($filtered) : null;
    }
}