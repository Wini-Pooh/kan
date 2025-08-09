<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the profile page
     */
    public function show()
    {
        $user = Auth::user();
        
        // Получаем все организации пользователя для навигации с информацией о владельцах
        $userOrganizations = $user->organizations()->with(['spaces', 'owner'])->get();
        
        // Получаем организации, которыми пользователь владеет
        $ownedOrganizations = $user->ownedOrganizations()->with(['spaces', 'owner'])->get();
        
        return view('profile.show', compact('user', 'userOrganizations', 'ownedOrganizations'));
    }

    /**
     * Update profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'min:1', 'max:150'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'company_name' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        // Обработка загрузки фото
        if ($request->hasFile('photo')) {
            // Удаляем старое фото, если оно есть
            if ($user->photo && Storage::exists('public/photos/' . $user->photo)) {
                Storage::delete('public/photos/' . $user->photo);
            }
            
            $photo = $request->file('photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photo->storeAs('public/photos', $photoName);
            $user->photo = $photoName;
        }

        // Обновляем остальные поля
        $user->update($request->except(['photo', 'password']));

        return redirect()->route('profile.show')->with('success', 'Профиль успешно обновлен!');
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();

        // Проверяем текущий пароль
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Текущий пароль введен неверно.']);
        }

        // Обновляем пароль
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.show')->with('success', 'Пароль успешно изменен!');
    }
}
