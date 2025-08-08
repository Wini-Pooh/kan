<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Organization;
use App\Models\Space;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем тестового пользователя
        $user = User::firstOrCreate([
            'phone' => '79999999999'
        ], [
            'name' => 'Тестовый Пользователь',
            'password' => Hash::make('password')
        ]);

        // Создаем тестовую организацию
        $organization = Organization::firstOrCreate([
            'name' => 'Тестовая Организация'
        ], [
            'description' => 'Описание тестовой организации',
            'owner_id' => $user->id
        ]);

        // Добавляем пользователя в организацию как владельца
        if (!$organization->members()->where('user_id', $user->id)->exists()) {
            $organization->members()->attach($user->id, [
                'role' => 'owner'
            ]);
        }

        // Создаем тестовое пространство
        $space = Space::firstOrCreate([
            'name' => 'Тестовое Пространство'
        ], [
            'description' => 'Описание тестового пространства',
            'organization_id' => $organization->id,
            'created_by' => $user->id,
            'visibility' => 'private'
        ]);

        // Добавляем пользователя в пространство как админа
        if (!$space->members()->where('user_id', $user->id)->exists()) {
            $space->members()->attach($user->id, [
                'role' => 'admin',
                'access_level' => 'full'
            ]);
        }

        // Создаем дополнительных пользователей для демонстрации участников
        for ($i = 2; $i <= 4; $i++) {
            $additionalUser = User::firstOrCreate([
                'phone' => '7999999999' . $i
            ], [
                'name' => 'Пользователь ' . $i,
                'password' => Hash::make('password')
            ]);

            // Добавляем в организацию
            if (!$organization->members()->where('user_id', $additionalUser->id)->exists()) {
                $organization->members()->attach($additionalUser->id, [
                    'role' => 'member'
                ]);
            }

            // Добавляем в пространство
            if (!$space->members()->where('user_id', $additionalUser->id)->exists()) {
                $space->members()->attach($additionalUser->id, [
                    'role' => 'member',
                    'access_level' => $i === 2 ? 'editor' : 'viewer'
                ]);
            }
        }

        $this->command->info('Тестовые данные успешно созданы!');
        $this->command->info('Пользователь: ' . $user->phone . ' / password');
    }
}
