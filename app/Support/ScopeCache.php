<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Center;
use App\Models\Halaqa;
use App\Models\Region;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * ذاكرة مؤقتة بعمر الطلب الواحد لنطاقات المستخدمين وكيانات الهرمية (فرع/منطقة/مركز/حلقة)،
 * لتفادي تكرار نفس الاستعلام عشرات المرات داخل نفس الطلب.
 * تُفرَّغ بعد كل طلب وعند أي تعديل على user_scopes (انظر AppServiceProvider و UserScope).
 */
class ScopeCache
{
    private const ENTITY_MODELS = [
        'branch' => Branch::class,
        'region' => Region::class,
        'center' => Center::class,
        'halaqa' => Halaqa::class,
    ];

    /** @var array<string, Collection> */
    private array $activeScopeIds = [];

    /** @var array<string, array<int|string, Model|null>> */
    private array $entities = [];

    /**
     * النطاقات النشطة لصاحب النطاق مجمّعة حسب النوع: ['branch' => [ids], 'region' => [ids], ...]
     */
    public function activeScopeIds(Model $owner): Collection
    {
        $key = $owner::class . ':' . $owner->getKey();

        return $this->activeScopeIds[$key] ??= $owner->scopes()
            ->active()
            ->get(['scope_type', 'scope_id'])
            ->groupBy('scope_type')
            ->map(fn($scopes) => $scopes->pluck('scope_id')->unique()->values());
    }

    /**
     * جلب كيان الهرمية (مع مراعاة نفس قيود الرؤية المطبّقة على Model::find) مرة واحدة لكل طلب
     */
    public function entity(string $type, $id): ?Model
    {
        $modelClass = self::ENTITY_MODELS[$type] ?? null;

        if (!$modelClass || $id === null) {
            return null;
        }

        if (!array_key_exists($id, $this->entities[$type] ?? [])) {
            $this->entities[$type][$id] = $this->entityQuery($type)->find($id);
        }

        return $this->entities[$type][$id];
    }

    /**
     * تحميل كيانات مجموعة نطاقات (وما فوقها في الهرمية) دفعة واحدة لكل نوع، بدل استعلام لكل نطاق.
     * يُستدعى قبل تحويل قائمة مستخدمين إلى Resources.
     *
     * @param iterable<\App\Models\UserScope> $scopes
     */
    public function prime(iterable $scopes): void
    {
        $ids = collect($scopes)->groupBy('scope_type')->map(fn($group) => $group->pluck('scope_id'));

        $halaqas = $this->loadMany('halaqa', $ids->get('halaqa', collect()));

        $centers = $this->loadMany('center', $ids->get('center', collect())->merge(
            $halaqas->filter(fn($h) => $h->reference_type?->value === 'center')->pluck('reference_id')
        ));

        $regions = $this->loadMany('region', $ids->get('region', collect())
            ->merge($halaqas->filter(fn($h) => $h->reference_type?->value === 'region')->pluck('reference_id'))
            ->merge($centers->pluck('region_id')));

        $this->loadMany('branch', $ids->get('branch', collect())->merge($regions->pluck('branch_id')));
    }

    private function loadMany(string $type, Collection $ids): Collection
    {
        $ids = $ids->filter()->unique()->values();
        $missing = $ids->reject(fn($id) => array_key_exists($id, $this->entities[$type] ?? []));

        if ($missing->isNotEmpty()) {
            $found = $this->entityQuery($type)->whereIn('id', $missing)->get()->keyBy('id');

            foreach ($missing as $id) {
                $this->entities[$type][$id] = $found->get($id);
            }
        }

        return $ids->map(fn($id) => $this->entities[$type][$id])->filter()->values();
    }

    private function entityQuery(string $type)
    {
        $query = (self::ENTITY_MODELS[$type])::query();

        // حالة الحلقة (lastStatus) تُحمَّل تلقائياً مع كل حلقة ولا حاجة لها هنا
        return $type === 'halaqa' ? $query->without('lastStatus') : $query;
    }

    public function flush(): void
    {
        $this->activeScopeIds = [];
        $this->entities = [];
    }
}
