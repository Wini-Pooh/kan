<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invitation;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    /**
     * Принятие приглашения по токену
     */
    public function accept($token)
    {
        $invitation = Invitation::with(['space', 'organization'])
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return redirect()->route('home')->with('error', 'Приглашение недействительно или истекло');
        }

        if (!Auth::check()) {
            // Если пользователь не авторизован, перенаправляем на страницу входа
            session(['invitation_token' => $token]);
            return redirect()->route('login')->with('message', 'Войдите или зарегистрируйтесь для принятия приглашения');
        }

        $user = Auth::user();

        if ($invitation->type === 'space') {
            $space = $invitation->space;
            $organization = $invitation->organization;
            
            if (!$space) {
                return redirect()->route('home')->with('error', 'Пространство не найдено или было удалено');
            }
            
            if (!$organization) {
                return redirect()->route('home')->with('error', 'Организация не найдена или была удалена');
            }
            
            // Добавляем пользователя в пространство со статусом pending (ожидание одобрения)
            if (!$space->members()->where('user_id', $user->id)->exists()) {
                $space->members()->attach($user->id, [
                    'role' => 'member',
                    'access_level' => 'full',
                    'status' => 'pending'
                ]);
            }

            // Добавляем пользователя в организацию, если он не состоит в ней
            if (!$organization->members()->where('user_id', $user->id)->exists()) {
                $organization->members()->attach($user->id, [
                    'role' => 'member'
                ]);
            }

            $invitation->update([
                'status' => 'accepted',
                'accepted_by' => $user->id,
                'accepted_at' => now()
            ]);

            return redirect()->route('home')->with('success', 'Заявка на присоединение отправлена. Ожидайте одобрения администратора пространства "' . $space->name . '"');
        }

        return redirect()->route('home')->with('error', 'Неизвестный тип приглашения');
    }
}
