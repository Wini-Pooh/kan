<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SimpleStorageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Показать простую страницу управления памятью
     */
    public function index()
    {
        $user = Auth::user();
        
        // Инициализируем поля если они пустые
        if (is_null($user->storage_limit_mb)) {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'storage_limit_mb' => 50,
                    'storage_used_mb' => 0,
                    'plan_type' => 'free',
                    'additional_storage_mb' => 0,
                ]);
                
            // Перезагружаем данные пользователя
            $user = Auth::user();
        }
        
        // Получаем организации пользователя для sidebar
        $userOrganizations = $user->organizations()->with(['spaces', 'owner'])->get();
        $ownedOrganizations = $user->ownedOrganizations()->with(['spaces'])->get();
        
        return view('memory.simple', compact('user', 'userOrganizations', 'ownedOrganizations'));
    }

    /**
     * Показать тарифные планы
     */
    public function plans()
    {
        $user = Auth::user();
                $userOrganizations = $user->organizations()->with(['spaces', 'owner'])->get();
        $ownedOrganizations = $user->ownedOrganizations()->with(['spaces'])->get();
        $plans = [
            [
                'id' => 0,
                'name' => 'free',
                'display_name' => 'Бесплатный план',
                'description' => 'Базовый бесплатный план',
                'storage_mb' => 50,
                'price' => 0.00,
                'features' => ['50 МБ памяти', 'Базовые функции', 'Сообщество поддержки']
            ],
            [
                'id' => 1,
                'name' => 'test',
                'display_name' => 'Тестовый план',
                'description' => 'Тестовый план с базовым набором функций',
                'storage_mb' => 1024,
                'price' => 99.00,
                'features' => ['1 ГБ памяти', 'До 5 пространств', 'Базовая поддержка']
            ],
            [
                'id' => 2,
                'name' => 'custom_1gb',
                'display_name' => 'Настраиваемый 1 ГБ',
                'description' => 'Дополнительный 1 ГБ памяти',
                'storage_mb' => 1024,
                'price' => 149.00,
                'features' => ['1 ГБ дополнительной памяти', 'Можно докупать блоками']
            ],
            [
                'id' => 3,
                'name' => 'custom_5gb',
                'display_name' => 'Настраиваемый 5 ГБ',
                'description' => 'Дополнительные 5 ГБ памяти',
                'storage_mb' => 5120,
                'price' => 599.00,
                'features' => ['5 ГБ дополнительной памяти', 'Выгоднее покупки по 1 ГБ']
            ]
        ];
        
        return view('memory.plans', compact('user', 'plans','userOrganizations', 'ownedOrganizations'));
    }

    /**
     * Демонстрация системы
     */
    public function demo()
    {
        $user = Auth::user();
        
        // Инициализируем поля если они пустые
        if (is_null($user->storage_limit_mb)) {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'storage_limit_mb' => 50,
                    'storage_used_mb' => 0,
                    'plan_type' => 'free',
                    'additional_storage_mb' => 0,
                ]);
                
            // Перезагружаем данные пользователя
            $user = Auth::user();
        }
        
        $totalLimit = $user->storage_limit_mb + $user->additional_storage_mb;
        $usagePercent = $totalLimit > 0 ? round(($user->storage_used_mb / $totalLimit) * 100, 2) : 0;
        $available = $totalLimit - $user->storage_used_mb;
        
        $stats = [
            'used_mb' => $user->storage_used_mb,
            'limit_mb' => $totalLimit,
            'available_mb' => $available,
            'usage_percent' => $usagePercent,
            'plan_type' => $user->plan_type ?? 'free'
        ];
        
        return view('memory.demo', compact('user', 'stats'));
    }

    /**
     * Симуляция действий
     */
    public function simulate(Request $request)
    {
        $request->validate([
            'action' => 'required|string',
            'size_mb' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $sizeMb = (float) $request->size_mb;
        
        $totalLimit = $user->storage_limit_mb + $user->additional_storage_mb;
        $currentUsed = $user->storage_used_mb;
        $newUsed = $currentUsed + $sizeMb;
        
        if ($newUsed > $totalLimit) {
            return response()->json([
                'success' => false,
                'message' => 'Недостаточно памяти. Доступно: ' . round($totalLimit - $currentUsed, 2) . ' МБ',
            ]);
        }
        
        // Обновляем использование памяти
        DB::table('users')
            ->where('id', $user->id)
            ->update(['storage_used_mb' => $newUsed]);
            
        // Создаем лог
        DB::table('storage_usage_logs')->insert([
            'user_id' => $user->id,
            'action' => $request->action,
            'entity_type' => 'demo',
            'storage_used_mb' => $sizeMb,
            'storage_before_mb' => $currentUsed,
            'storage_after_mb' => $newUsed,
            'description' => 'Демо: ' . $request->action,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $newUsagePercent = $totalLimit > 0 ? round(($newUsed / $totalLimit) * 100, 2) : 0;
        
        return response()->json([
            'success' => true,
            'message' => 'Операция выполнена успешно',
            'stats' => [
                'used_mb' => $newUsed,
                'limit_mb' => $totalLimit,
                'available_mb' => $totalLimit - $newUsed,
                'usage_percent' => $newUsagePercent,
                'formatted_used' => $newUsed >= 1024 ? round($newUsed / 1024, 2) . ' ГБ' : round($newUsed, 2) . ' МБ',
            ]
        ]);
    }

    /**
     * Покупка тарифного плана
     */
    public function purchasePlan(Request $request)
    {
        $request->validate([
            'plan_name' => 'required|string|in:test,custom_1gb,custom_5gb',
        ]);

        $user = Auth::user();
        $planName = $request->plan_name;
        
        // Определяем параметры плана
        $plans = [
            'test' => [
                'storage_mb' => 1024,
                'price' => 99.00,
                'display_name' => 'Тестовый план'
            ],
            'custom_1gb' => [
                'storage_mb' => 1024,
                'price' => 149.00,
                'display_name' => 'Настраиваемый 1 ГБ'
            ],
            'custom_5gb' => [
                'storage_mb' => 5120,
                'price' => 599.00,
                'display_name' => 'Настраиваемый 5 ГБ'
            ]
        ];

        if (!isset($plans[$planName])) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный план'
            ], 400);
        }

        $plan = $plans[$planName];
        
        // Обновляем данные пользователя
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'plan_type' => $planName,
                'additional_storage_mb' => $plan['storage_mb'],
                'plan_expires_at' => now()->addMonth(), // План действует месяц
                'updated_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => "План \"{$plan['display_name']}\" успешно активирован! Добавлено {$plan['storage_mb']} МБ памяти.",
            'plan_name' => $plan['display_name'],
            'storage_added' => $plan['storage_mb']
        ]);
    }

    /**
     * Сброс демо данных
     */
    public function resetDemo()
    {
        $user = Auth::user();
        
        // Удаляем демо логи
        DB::table('storage_usage_logs')
            ->where('user_id', $user->id)
            ->where('entity_type', 'demo')
            ->delete();
            
        // Пересчитываем использование
        $realUsage = DB::table('storage_usage_logs')
            ->where('user_id', $user->id)
            ->where('entity_type', '!=', 'demo')
            ->sum('storage_used_mb');
            
        DB::table('users')
            ->where('id', $user->id)
            ->update(['storage_used_mb' => max(0, $realUsage)]);
        
        return response()->json([
            'success' => true,
            'message' => 'Демо данные сброшены',
        ]);
    }
}
