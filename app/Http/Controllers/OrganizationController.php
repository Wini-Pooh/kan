<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Создание новой организации
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        $organization = Organization::create([
            'name' => $request->name,
            'description' => $request->description,
            'owner_id' => Auth::id()
        ]);

        // Добавляем создателя как владельца организации
        $organization->members()->attach(Auth::id(), [
            'role' => 'owner'
        ]);

        return redirect()->route('home')->with('success', 'Организация успешно создана!');
    }
}
