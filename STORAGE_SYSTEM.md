# Система управления памятью для Kanban доски

## Что реализовано

### 1. Основные компоненты
- ✅ **Миграции БД**: Добавлены поля памяти в таблицу users
- ✅ **Модели**: User, SubscriptionPlan, UserSubscription, StorageUsageLog  
- ✅ **Сервис**: StorageService для учета потребления памяти
- ✅ **Контроллеры**: StorageController, StorageTestController
- ✅ **Представления**: Страницы управления памятью и тарифных планов

### 2. Система учета памяти
- ✅ **Создание пространства**: +5 МБ
- ✅ **Создание задачи**: +0.1 МБ + размер контента
- ✅ **Загрузка файлов**: размер файла
- ✅ **Логирование операций**: Все операции записываются в storage_usage_logs

### 3. Тарифные планы
- ✅ **Бесплатный план**: 50 МБ (по умолчанию)
- ✅ **Тестовый план**: 1 ГБ за 99 руб/месяц
- ✅ **Настраиваемые планы**: 1 ГБ, 5 ГБ, 10 ГБ

### 4. Интерфейс
- ✅ **Боковая панель**: Виджет памяти с индикацией
- ✅ **Страница управления**: /storage - детальная статистика
- ✅ **Страница планов**: /storage/plans - покупка тарифов
- ✅ **Демо страница**: /storage/demo - тестирование системы

## Как протестировать

### 1. Запустить сидер тарифных планов
```bash
php artisan db:seed --class=SubscriptionPlansSeeder
```

### 2. Инициализировать память для существующих пользователей
```bash
php artisan storage:initialize
```

### 3. Открыть демо страницу
Перейти на `/storage/demo` и попробовать:
- Создание пространства (+5 МБ)
- Создание задачи (+0.1 МБ)  
- Загрузка файла (+1 МБ)

### 4. Проверить основные страницы
- `/storage` - управление памятью
- `/storage/plans` - тарифные планы

## Основные методы User модели

```php
// Получить общий лимит памяти
$user->total_storage_limit

// Получить доступную память  
$user->available_storage

// Процент использования
$user->storage_usage_percent

// Проверить хватает ли памяти
$user->hasEnoughStorage(5.0)

// Увеличить потребление
$user->increaseStorageUsage(5.0, 'create_space', 'space', $spaceId)

// Уменьшить потребление
$user->decreaseStorageUsage(5.0, 'delete_space', 'space', $spaceId)
```

## Интеграция в существующий код

В контроллерах где создаются ресурсы, добавить:

```php
use App\Services\StorageService;

public function store(Request $request) {
    $storageService = new StorageService();
    $user = Auth::user();
    
    // Проверка лимита перед созданием
    if (!$storageService->checkStorageLimit($user, 5.0)) {
        return back()->with('error', 'Недостаточно памяти');
    }
    
    // Создание ресурса
    $space = Space::create([...]);
    
    // Учет памяти
    $storageService->trackSpaceCreation($user, $space);
}
```

## Что осталось доделать

1. **Интеграция в TaskViewController** - добавить учет файлов
2. **Middleware** - автоматическая проверка лимитов  
3. **Система платежей** - реальная интеграция с платежными системами
4. **Уведомления** - предупреждения о превышении лимитов
5. **API** - REST API для мобильных приложений

## Файловая структура

```
app/
├── Console/Commands/
│   └── InitializeUserStorage.php     # Команда инициализации
├── Http/Controllers/
│   ├── StorageController.php         # Основной контроллер
│   └── StorageTestController.php     # Демо контроллер
├── Http/Middleware/
│   └── CheckStorageLimit.php         # Middleware проверки лимитов
├── Models/
│   ├── SubscriptionPlan.php          # Модель тарифных планов
│   ├── UserSubscription.php          # Модель подписок
│   └── StorageUsageLog.php           # Модель логов памяти
└── Services/
    └── StorageService.php            # Сервис учета памяти

database/
├── migrations/
│   ├── 2025_08_10_120000_add_storage_fields_to_users_table.php
│   ├── 2025_08_10_120001_create_subscription_plans_table.php
│   ├── 2025_08_10_120002_create_user_subscriptions_table.php
│   └── 2025_08_10_120003_create_storage_usage_logs_table.php
└── seeders/
    └── SubscriptionPlansSeeder.php   # Базовые тарифные планы

resources/views/
├── storage/
│   ├── index.blade.php               # Страница управления памятью
│   ├── plans.blade.php               # Страница тарифных планов
│   └── demo.blade.php                # Демо страница
└── components/
    └── storage-widget.blade.php      # Виджет памяти
```

Система готова к тестированию! 🚀
