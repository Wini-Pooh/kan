<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PhoneRegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        return view('auth.phone-register');
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        $user = $this->create($request->all());

        Auth::login($user);

        return redirect()->route('home');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'unique:users', function ($attribute, $value, $fail) {
                $phone = $this->formatPhone($value);
                if (strlen($phone) !== 11 || substr($phone, 0, 1) !== '7') {
                    $fail('Неверный формат номера телефона.');
                }
            }],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Поле имя обязательно для заполнения.',
            'phone.required' => 'Поле номер телефона обязательно для заполнения.',
            'phone.unique' => 'Пользователь с таким номером телефона уже существует.',
            'email.email' => 'Неверный формат email.',
            'email.unique' => 'Пользователь с таким email уже существует.',
            'password.required' => 'Поле пароль обязательно для заполнения.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
            'password.confirmed' => 'Пароли не совпадают.',
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'phone' => $this->formatPhone($data['phone']),
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Форматирование номера телефона
     */
    protected function formatPhone($phone)
    {
        // Удаляем все символы кроме цифр
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Если номер начинается с 8, заменяем на 7
        if (substr($phone, 0, 1) === '8') {
            $phone = '7' . substr($phone, 1);
        }
        
        // Если номер начинается с +7, убираем +
        if (substr($phone, 0, 2) === '+7') {
            $phone = substr($phone, 1);
        }
        
        // Если номер не начинается с 7, добавляем 7
        if (substr($phone, 0, 1) !== '7') {
            $phone = '7' . $phone;
        }
        
        return $phone;
    }
}
