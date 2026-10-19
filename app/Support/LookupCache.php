<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * ذاكرة مؤقتة بعمر الطلب الواحد لجلب سجلات الجداول المرجعية (ثوابت، سور...) بالمعرّف،
 * بديلاً عن تكرار Model::find لنفس السجل في كل صف من صفوف القائمة.
 * تُفرَّغ بعد كل طلب (انظر AppServiceProvider).
 */
class LookupCache
{
    /** @var array<class-string<Model>, array<int|string, Model|null>> */
    private array $records = [];

    /**
     * @param class-string<Model> $modelClass
     */
    public function find(string $modelClass, $id): ?Model
    {
        if ($id === null) {
            return null;
        }

        if (!array_key_exists($id, $this->records[$modelClass] ?? [])) {
            $this->records[$modelClass][$id] = $modelClass::find($id);
        }

        return $this->records[$modelClass][$id];
    }

    public function flush(): void
    {
        $this->records = [];
    }
}
