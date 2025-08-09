<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Column;
use App\Models\Space;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ColumnController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Создание новой колонки
     */
    public function store(Request $request, Space $space)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/'
        ]);

        // Проверяем права доступа к пространству
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }

        $slug = Str::slug($request->name);
        
        // Проверяем уникальность slug в рамках пространства
        $originalSlug = $slug;
        $counter = 1;
        while ($space->columns()->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $column = Column::create([
            'name' => $request->name,
            'slug' => $slug,
            'color' => $request->color ?? '#6c757d',
            'position' => Column::getNextPosition($space->id),
            'space_id' => $space->id,
            'created_by' => Auth::id(),
            'is_default' => false
        ]);

        return response()->json([
            'success' => true,
            'column' => $column,
            'message' => 'Колонка успешно создана'
        ]);
    }

    /**
     * Обновление колонки
     */
    public function update(Request $request, Space $space, Column $column)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/'
        ]);

        // Проверяем права доступа
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }

        if ($column->space_id !== $space->id) {
            abort(404, 'Колонка не найдена в данном пространстве');
        }

        $slug = Str::slug($request->name);
        
        // Проверяем уникальность slug в рамках пространства (исключая текущую колонку)
        $originalSlug = $slug;
        $counter = 1;
        while ($space->columns()->where('slug', $slug)->where('id', '!=', $column->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $column->update([
            'name' => $request->name,
            'slug' => $slug,
            'color' => $request->color ?? $column->color
        ]);

        return response()->json([
            'success' => true,
            'column' => $column,
            'message' => 'Колонка успешно обновлена'
        ]);
    }

    /**
     * Простое обновление колонки (без пространства в URL)
     */
    public function updateSimple(Request $request, $id)
    {
        try {
            $column = Column::findOrFail($id);
            
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'color' => 'sometimes|required|string|max:7'
            ]);
            
            if ($request->has('name')) {
                $column->name = $request->name;
                $column->slug = Str::slug($request->name);
            }
            
            if ($request->has('color')) {
                $column->color = $request->color;
            }
            
            $column->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Колонка обновлена успешно',
                'column' => $column
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении колонки: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Удаление колонки
     */ 
    public function destroy(Space $space, Column $column)
    {
        Log::info('Column deletion attempt', [
            'space_id' => $space->id,
            'column_id' => $column->id,
            'user_id' => Auth::id(),
            'tasks_count' => $column->tasks()->count(),
            'can_be_deleted' => $column->canBeDeleted()
        ]);

        // Проверяем права доступа
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }

        if ($column->space_id !== $space->id) {
            abort(404, 'Колонка не найдена в данном пространстве');
        }

        // Проверяем, можно ли удалить колонку
        if (!$column->canBeDeleted()) {
            Log::warning('Cannot delete default column', ['column_id' => $column->id]);
            return response()->json([
                'success' => false,
                'message' => 'Нельзя удалить встроенную колонку'
            ], 400);
        }

        // Архивируем все задачи в колонке перед её скрытием
        $tasksCount = $column->allTasks()->whereNull('archived_at')->count();
        if ($tasksCount > 0) {
            // Архивируем задачи, но сохраняем ссылку на колонку
            $column->allTasks()->whereNull('archived_at')->update([
                'archived_at' => now(),
                'archived_by' => Auth::id()
            ]);
            
            Log::info('Tasks archived before column hiding', [
                'column_id' => $column->id,
                'archived_tasks_count' => $tasksCount
            ]);
        }

        // Вместо удаления колонки, помечаем её как скрытую
        $column->update([
            'is_hidden' => true
        ]);
        
        Log::info('Column hidden (soft deleted)', [
            'column_id' => $column->id,
            'archived_tasks_count' => $tasksCount
        ]);

        $message = $tasksCount > 0 
            ? "Колонка успешно скрыта. {$tasksCount} задач(и) были автоматически архивированы. Вы можете найти их в архиве."
            : 'Колонка успешно скрыта';

        return response()->json([
            'success' => true,
            'message' => $message,
            'archived_tasks_count' => $tasksCount
        ]);
    }

    /**
     * Получение всех колонок пространства
     */
    public function index(Space $space)
    {
        // Проверяем права доступа
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }

        $columns = $space->columns()
            ->visible() // Показываем только видимые колонки
            ->ordered()
            ->withCount('tasks')
            ->get();

        return response()->json([
            'success' => true,
            'columns' => $columns
        ]);
    }

    /**
     * Массовое обновление позиций колонок
     */
    public function updatePositions(Request $request, Space $space)
    {
        $request->validate([
            'columns' => 'required|array',
            'columns.*.id' => 'required|integer|exists:columns,id',
            'columns.*.position' => 'required|integer'
        ]);

        // Проверяем права доступа
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }

        foreach ($request->columns as $columnData) {
            $column = Column::find($columnData['id']);
            
            if ($column && $column->space_id === $space->id) {
                $column->update(['position' => $columnData['position']]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Позиции колонок обновлены'
        ]);
    }

    /**
     * Создание стандартных колонок для нового пространства
     */
    public static function createDefaultColumns(Space $space, $userId)
    {
        $defaultColumns = [
            ['name' => 'К выполнению', 'slug' => 'todo', 'color' => '#dc3545', 'position' => 1],
            ['name' => 'В процессе', 'slug' => 'progress', 'color' => '#fd7e14', 'position' => 2],
            ['name' => 'Выполнено', 'slug' => 'done', 'color' => '#28a745', 'position' => 3],
        ];

        foreach ($defaultColumns as $columnData) {
            Column::create([
                'name' => $columnData['name'],
                'slug' => $columnData['slug'],
                'color' => $columnData['color'],
                'position' => $columnData['position'],
                'space_id' => $space->id,
                'created_by' => $userId,
                'is_default' => true
            ]);
        }
    }

    /**
     * Перемещение колонки влево или вправо
     */
    public function move(Request $request, $id)
    {
        try {
            $column = Column::findOrFail($id);
            
            $request->validate([
                'direction' => 'required|in:left,right'
            ]);
            
            $direction = $request->direction;
            $currentPosition = $column->position;
            
            if ($direction === 'left' && $currentPosition > 1) {
                // Ищем колонку слева
                $leftColumn = Column::where('space_id', $column->space_id)
                    ->where('position', $currentPosition - 1)
                    ->first();
                
                if ($leftColumn) {
                    // Меняем позиции местами
                    $leftColumn->position = $currentPosition;
                    $column->position = $currentPosition - 1;
                    
                    $leftColumn->save();
                    $column->save();
                }
            } elseif ($direction === 'right') {
                // Ищем колонку справа
                $rightColumn = Column::where('space_id', $column->space_id)
                    ->where('position', $currentPosition + 1)
                    ->first();
                
                if ($rightColumn) {
                    // Меняем позиции местами
                    $rightColumn->position = $currentPosition;
                    $column->position = $currentPosition + 1;
                    
                    $rightColumn->save();
                    $column->save();
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Колонка перемещена успешно'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при перемещении колонки: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Восстановление удаленной (скрытой) колонки
     */
    public function restore(Request $request, Space $space, $columnId)
    {
        try {
            // Находим скрытую колонку
            $column = Column::where('id', $columnId)
                ->where('space_id', $space->id)
                ->where('is_hidden', true)
                ->firstOrFail();
            
            // Проверяем права доступа
            if (!$space->members()->where('user_id', Auth::id())->exists()) {
                abort(403, 'У вас нет доступа к этому пространству');
            }
            
            // Восстанавливаем колонку
            $column->update([
                'is_hidden' => false
            ]);
            
            Log::info('Column restored from deleted state', [
                'column_id' => $column->id,
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Колонка успешно восстановлена',
                'column' => $column
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при восстановлении колонки: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Получение списка удаленных (скрытых) колонок пространства
     */
    public function hiddenColumns(Space $space)
    {
        // Проверяем права доступа
        if (!$space->members()->where('user_id', Auth::id())->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }

        $columns = $space->columns()
            ->hidden() // Показываем только скрытые колонки
            ->ordered()
            ->withCount('tasks')
            ->get();

        return response()->json([
            'success' => true,
            'columns' => $columns
        ]);
    }
}
