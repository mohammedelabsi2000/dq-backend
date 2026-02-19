<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class UserRole extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'relation_type',
        'relation_id',
        'from_date',
        'to_date',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date'   => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // الدور
    public function role()
    {
        return $this->belongsTo(Constant::class, 'role_id');
    }

    // العلاقة polymorphic (Halaqa, Branch, etc...)
    public function relation()
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    // الأدوار النشطة فقط
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('to_date')
              ->orWhere('to_date', '>=', Carbon::today());
        });
    }

    // حسب نوع العلاقة (مثلاً Halaqa::class)
    public function scopeForRelation(Builder $query, string $relationType): Builder
    {
        return $query->where('relation_type', $relationType);
    }

    // حسب كيان محدد
    public function scopeForModel(Builder $query, Model $model): Builder
    {
        return $query->where('relation_type', $model->getMorphClass())
                     ->where('relation_id', $model->getKey());
    }

    // حسب دور معين (name)
    public function scopeForRole(Builder $query, string $roleName): Builder
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    // هل الدور نشط حالياً؟
    public function isActive(): bool
    {
        return is_null($this->to_date) || $this->to_date >= Carbon::today();
    }
}