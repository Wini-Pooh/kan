<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'test',
                'display_name' => 'Тестовый план',
                'description' => 'Тестовый план с базовым набором функций',
                'storage_mb' => 1024, // 1 ГБ
                'price' => 99.00,
                'currency' => 'RUB',
                'duration_days' => 30,
                'is_active' => true,
                'is_recurring' => false,
                'features' => [
                    'storage' => '1 ГБ памяти',
                    'spaces' => 'До 5 пространств',
                    'members' => 'До 10 участников',
                    'support' => 'Базовая поддержка',
                ],
            ],
            [
                'name' => 'custom_1gb',
                'display_name' => 'Настраиваемый 1 ГБ',
                'description' => 'Дополнительный 1 ГБ памяти',
                'storage_mb' => 1024, // 1 ГБ
                'price' => 149.00,
                'currency' => 'RUB',
                'duration_days' => 30,
                'is_active' => true,
                'is_recurring' => false,
                'features' => [
                    'storage' => '1 ГБ дополнительной памяти',
                    'expandable' => 'Можно докупать блоками',
                ],
            ],
            [
                'name' => 'custom_5gb',
                'display_name' => 'Настраиваемый 5 ГБ',
                'description' => 'Дополнительные 5 ГБ памяти',
                'storage_mb' => 5120, // 5 ГБ
                'price' => 599.00,
                'currency' => 'RUB',
                'duration_days' => 30,
                'is_active' => true,
                'is_recurring' => false,
                'features' => [
                    'storage' => '5 ГБ дополнительной памяти',
                    'expandable' => 'Можно докупать блоками',
                    'discount' => 'Выгоднее покупки по 1 ГБ',
                ],
            ],
            [
                'name' => 'custom_10gb',
                'display_name' => 'Настраиваемый 10 ГБ',
                'description' => 'Дополнительные 10 ГБ памяти',
                'storage_mb' => 10240, // 10 ГБ
                'price' => 999.00,
                'currency' => 'RUB',
                'duration_days' => 30,
                'is_active' => true,
                'is_recurring' => false,
                'features' => [
                    'storage' => '10 ГБ дополнительной памяти',
                    'expandable' => 'Можно докупать блоками',
                    'discount' => 'Максимальная выгода',
                    'priority_support' => 'Приоритетная поддержка',
                ],
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(
                ['name' => $planData['name']],
                $planData
            );
        }
    }
}
