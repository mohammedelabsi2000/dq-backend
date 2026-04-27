<?php

namespace App\Concerns;

use App\Enums\AuditEvent;
use App\Models\Audit;

trait Auditable
{
    /**
     * Boot the Auditable trait to listen for model events and create audit records
     * @return void
     */
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->audit(AuditEvent::Created);
        });

        static::updated(function ($model) {
            $model->audit(AuditEvent::Updated);
        });

        static::deleted(function ($model) {
            $model->audit(AuditEvent::Deleted);
        });

        static::restored(function ($model) {
            $model->audit(AuditEvent::Restored);
        });
    }

    /**
     * Create an audit record for the given event
     * @param mixed $event
     * @return void
     */
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

    /**
     * Cast an array of values for audit purposes
     * @param array $values
     * @return bool|string|null
     */
    protected function castArrayForAudit(array $values): ?string
    {
        // نحذف أي قيمة غير قابلة للتخزين في JSON
        $filtered = array_filter($values, function ($value) {
            return !is_object($value) && !is_resource($value);
        });

        return !empty($filtered) ? json_encode($filtered) : null;
    }
}
