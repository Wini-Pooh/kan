<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Space;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        
        // Получаем пространства, где пользователь является участником
        $userSpaces = $user->spaces()->with(['organization.owner', 'creator', 'members'])->get();
        
        // Получаем все организации пользователя с информацией о владельцах
        $userOrganizations = $user->organizations()->with(['spaces', 'owner'])->get();
        
        // Получаем организации, которыми пользователь владеет (может создавать пространства)
        $ownedOrganizations = $user->ownedOrganizations()->with(['spaces', 'owner'])->get();
        
        return view('home', compact('userSpaces', 'userOrganizations', 'ownedOrganizations'));
    }

    /**
     * Show specific organization and its spaces
     */
    public function showOrganization(Organization $organization)
    {
        $user = Auth::user();
        
        // Проверяем, что пользователь является участником этой организации
        if (!$organization->members()->where('user_id', $user->id)->exists()) {
            abort(403, 'У вас нет доступа к этой организации');
        }
        
        // Получаем пространства организации
        if ($organization->owner_id === $user->id) {
            // Владелец организации видит ВСЕ пространства организации
            $spaces = $organization->spaces()
                ->with(['members', 'creator'])
                ->get();
        } else {
            // Обычные участники видят только пространства, к которым у них есть доступ
            $spaces = $organization->spaces()
                ->whereHas('members', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->with(['members', 'creator'])
                ->get();
        }
        
        // Получаем все организации пользователя для навигации с информацией о владельцах
        $userOrganizations = $user->organizations()->with(['spaces', 'owner'])->get();
        
        // Получаем организации, которыми пользователь владеет
        $ownedOrganizations = $user->ownedOrganizations()->with(['spaces', 'owner'])->get();
        
        return view('organization', compact('organization', 'spaces', 'userOrganizations', 'ownedOrganizations'));
    }

    /**
     * Show specific space
     */
    public function showSpace(Organization $organization, Space $space)
    {
        $user = Auth::user();
        
        // Проверяем, что пользователь является участником этого пространства
        if (!$space->members()->where('user_id', $user->id)->exists()) {
            abort(403, 'У вас нет доступа к этому пространству');
        }
        
        // Проверяем, что пространство принадлежит указанной организации
        if ($space->organization_id !== $organization->id) {
            abort(404, 'Пространство не найдено в данной организации');
        }
        
        $space->load(['members', 'creator', 'organization', 'columns']);
        
        // Получаем колонки с задачами (только неархивированными и видимые колонки)
        $columns = $space->columns()
            ->visible() // Показываем только видимые колонки
            ->ordered()
            ->with(['tasks' => function($query) {
                $query->notArchived()
                      ->with(['assignedUser', 'assignee'])
                      ->orderBy('position')
                      ->orderBy('created_at', 'desc');
            }])
            ->get();
        
        // Для обратной совместимости, группируем задачи по статусу (только неархивированные)
        $tasks = $space->activeTasks()
            ->orderBy('position')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('status');
        
        // Получаем все организации пользователя для навигации с информацией о владельцах
        $userOrganizations = $user->organizations()->with(['spaces', 'owner'])->get();
        
        // Получаем организации, которыми пользователь владеет
        $ownedOrganizations = $user->ownedOrganizations()->with(['spaces', 'owner'])->get();
        
        return view('space', compact('space', 'organization', 'userOrganizations', 'ownedOrganizations', 'columns', 'tasks'));
    }

    /**
     * Show space archive
     */
    public function showSpaceArchive(Organization $organization, Space $space)
    {
        $user = Auth::user();
        
        // Проверяем, что пользователь является участником пространства
        if (!$space->members()->where('user_id', $user->id)->exists()) {
            abort(403, 'У вас нет доступа к архиву этого пространства');
        }
        
        // Проверяем, что пространство принадлежит указанной организации
        if ($space->organization_id !== $organization->id) {
            abort(404, 'Пространство не найдено в данной организации');
        }
        
        $space->load(['creator', 'organization']);
        
        // Получаем архивированные задачи
        $archivedTasks = $space->archivedTasks()
            ->with(['creator', 'assignee', 'archiver', 'column'])
            ->orderBy('archived_at', 'desc')
            ->get();
        
        // Получаем все организации пользователя для навигации с информацией о владельцах
        $userOrganizations = $user->organizations()->with(['spaces', 'owner'])->get();
        
        // Получаем организации, которыми пользователь владеет
        $ownedOrganizations = $user->ownedOrganizations()->with(['spaces', 'owner'])->get();
        
        return view('space-archive', compact('space', 'organization', 'userOrganizations', 'ownedOrganizations', 'archivedTasks'));
    }
}
