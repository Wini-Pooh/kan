<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\StorageService;
use Illuminate\Support\Facades\Auth;

class CheckStorageLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $storageService = new StorageService();
            
            // Проверяем, не превышен ли лимит памяти
            if ($user->storage_usage_percent > 95) {
                // Если превышен критический лимит, блокируем создание новых ресурсов
                $restrictedRoutes = [
                    'spaces.store',
                    'tasks.store', 
                    'tasks.upload',
                ];
                
                if (in_array($request->route()->getName(), $restrictedRoutes)) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Превышен лимит памяти. Удалите ненужные файлы или обновите тарифный план.',
                            'storage_info' => [
                                'used_percent' => $user->storage_usage_percent,
                                'used_mb' => $user->storage_used_mb,
                                'limit_mb' => $user->total_storage_limit,
                            ]
                        ], 413);
                    }
                    
                    return redirect()->back()->with('error', 
                        'Превышен лимит памяти (' . round($user->storage_usage_percent, 1) . '%). ' .
                        'Удалите ненужные файлы или <a href="' . route('storage.plans') . '">обновите тарифный план</a>.'
                    );
                }
            }
        }

        return $next($request);
    }
}
