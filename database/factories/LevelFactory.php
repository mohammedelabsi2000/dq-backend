<?php

namespace Database\Factories;

use App\Enums\PeriodUnit;
use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Level>
 */
class LevelFactory extends Factory
{
    protected static array $usedOrders = [];
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $plan = Plan::inRandomOrder()->first();

        $planId = $plan ? $plan->id : null;

        // تهيئة التتبع لهذه الخطة عند أول استخدام
        if (!isset(static::$usedOrders[$planId])) {
            // جلب الأرقام الموجودة مسبقاً في قاعدة البيانات
            static::$usedOrders[$planId] = $plan
                ? $plan->levels()->pluck('order')->toArray()
                : [];
        }

        // إيجاد أول رقم ترتيب غير مستخدم
        $order = 1;
        while (in_array($order, static::$usedOrders[$planId])) {
            $order++;
        }
        // حفظ الرقم المستخدم لتجنب تكراره
        static::$usedOrders[$planId][] = $order;

        return [
            'plan_id' => $plan ? $plan->id : null,
            'name' => $this->faker->sentence(2),
            'order' => $plan->levels()->count() + 1,
            'period_unit' => $this->faker->randomElement(PeriodUnit::cases())->value,
            'period' => $this->faker->numberBetween(1, 12),
            'min_period' => $this->faker->optional()->numberBetween(1, 6),
            'max_period' => $this->faker->optional()->numberBetween(6, 24),
            'notes' => $this->faker->optional()->paragraph(),
        ];
    }
}
