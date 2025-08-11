<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Space;
use App\Models\Organization;
use App\Models\Invitation;
use App\Services\StorageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SpaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Создание нового пространства
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'organization_id' => 'required|exists:organizations,id'
        ]);

        $user = Auth::user();
        $storageService = new StorageService();

        // Проверяем лимит памяти перед созданием пространства
        if (!$storageService->checkStorageLimit($user, StorageService::SPACE_CREATION_SIZE_MB)) {
            return redirect()->back()->with('error', 
                'Недостаточно места для создания пространства. Требуется: ' . 
                StorageService::SPACE_CREATION_SIZE_MB . ' МБ, доступно: ' . 
                $user->available_storage . ' МБ'
            );
        }

        // Проверяем, что пользователь является владельцем организации
        $organization = Organization::findOrFail($request->organization_id);
        if ($organization->owner_id !== Auth::id()) {
            return redirect()->route('home')->with('error', 'Вы можете создавать пространства только в своих организациях.');
        }

        $space = Space::create([
            'name' => $request->name,
            'description' => $request->description,
            'organization_id' => $request->organization_id,
            'created_by' => Auth::id(),
            'visibility' => 'private'
        ]);

        // Добавляем создателя как админа пространства с активным статусом
        $space->members()->attach(Auth::id(), [
            'role' => 'admin',
            'access_level' => 'full',
            'status' => 'active'
        ]);

        // Учитываем потребление памяти
        $storageService->trackSpaceCreation($user, $space);

        return redirect()->route('organizations.show', $organization)->with('success', 'Пространство успешно создано!');
    }

    /**
     * Генерация ссылки приглашения
     */
    public function generateInviteLink(Space $space)
    {
        // Загружаем связь с организацией если не загружена
        $space->load('organization');
        
        // Проверяем права доступа: владелец организации или админ пространства
        $isOwner = $space->organization->owner_id === Auth::id();
        $isAdmin = $space->members()->where('user_id', Auth::id())->first()?->pivot?->role === 'admin';
        
        if (!$isOwner && !$isAdmin) {
            abort(403, 'У вас нет прав для создания приглашений в этом пространстве');
        }

        $invitation = Invitation::create([
            'space_id' => $space->id,
            'organization_id' => $space->organization_id,
            'invited_by' => Auth::id(),
            'token' => Str::uuid(),
            'expires_at' => now()->addDays(7),
            'type' => 'space'
        ]);

        $inviteUrl = route('invitations.accept', ['token' => $invitation->token]);

        return response()->json([
            'invite_url' => $inviteUrl,
            'expires_at' => $invitation->expires_at->format('d.m.Y H:i')
        ]);
    }

    /**
     * Удаление участника из пространства
     */
    public function removeMember(Space $space, $userId)
    {
        // Загружаем связь с организацией если не загружена
        $space->load('organization');
        
        // Проверяем права доступа: владелец организации или админ пространства
        $isOwner = $space->organization->owner_id === Auth::id();
        $isAdmin = $space->members()->where('user_id', Auth::id())->first()?->pivot?->role === 'admin';
        
        if (!$isOwner && !$isAdmin) {
            abort(403, 'У вас нет прав для удаления участников');
        }

        $space->members()->detach($userId);

        return response()->json(['success' => true]);
    }

    /**
     * Изменение уровня доступа участника
     */
    public function updateMemberAccess(Space $space, $userId, Request $request)
    {
        $request->validate([
            'access_level' => 'required|in:viewer,editor,admin'
        ]);

        // Загружаем связь с организацией если не загружена
        $space->load('organization');
        
        // Проверяем права доступа: владелец организации или админ пространства
        $isOwner = $space->organization->owner_id === Auth::id();
        $isAdmin = $space->members()->where('user_id', Auth::id())->first()?->pivot?->role === 'admin';
        
        if (!$isOwner && !$isAdmin) {
            abort(403, 'У вас нет прав для изменения доступа участников');
        }

        $role = $request->access_level === 'admin' ? 'admin' : 'member';

        $space->members()->updateExistingPivot($userId, [
            'role' => $role,
            'access_level' => $request->access_level
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Одобрение участника пространства
     */
    public function approveMember(Space $space, $userId)
    {
        // Загружаем связь с организацией если не загружена
        $space->load('organization');
        
        // Проверяем права доступа: владелец организации или админ пространства
        $isOwner = $space->organization->owner_id === Auth::id();
        $isAdmin = $space->members()->where('user_id', Auth::id())->first()?->pivot?->role === 'admin';
        
        if (!$isOwner && !$isAdmin) {
            abort(403, 'У вас нет прав для одобрения участников');
        }

        $space->members()->updateExistingPivot($userId, [
            'status' => 'active'
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Отклонение/блокировка участника пространства
     */
    public function banMember(Space $space, $userId)
    {
        // Загружаем связь с организацией если не загружена
        $space->load('organization');
        
        // Проверяем права доступа: владелец организации или админ пространства
        $isOwner = $space->organization->owner_id === Auth::id();
        $isAdmin = $space->members()->where('user_id', Auth::id())->first()?->pivot?->role === 'admin';
        
        if (!$isOwner && !$isAdmin) {
            abort(403, 'У вас нет прав для блокировки участников');
        }

        $space->members()->updateExistingPivot($userId, [
            'status' => 'banned'
        ]);

        return response()->json(['success' => true]);
    }
}
