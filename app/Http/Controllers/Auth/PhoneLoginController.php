<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PhoneLoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.phone-login');
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->attemptLogin($request)) {
            $request->session()->regenerate();
            return $this->sendLoginResponse($request);
        }

        return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ], [
            'phone.required' => 'Поле номер телефона обязательно для заполнения.',
            'password.required' => 'Поле пароль обязательно для заполнения.',
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        // Очищаем номер телефона от лишних символов
        $phone = $this->formatPhone($request->phone);
        
        return Auth::attempt(
            ['phone' => $phone, 'password' => $request->password],
            $request->filled('remember')
        );
    }

    protected function sendLoginResponse(Request $request)
    {
        return redirect()->intended(route('home'));
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'phone' => [trans('auth.failed')],
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
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
