<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\ValidationPatterns;
use App\Models\User;
use App\Models\OrganizationalUnit;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        $allowedUnitIds = [3, 4, 6, 7]; // DEV, INFRA, MKT, SALES
        $organizationalUnits = OrganizationalUnit::whereIn('id', $allowedUnitIds)
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'code' => $unit->code,
                    'description' => $unit->description,
                    'level' => $unit->level,
                    'display_name' => str_repeat('  ', $unit->level - 1) . $unit->name . ' (' . $unit->code . ')'
                ];
            });

        return Inertia::render('auth/Register', [
            'organizationalUnits' => $organizationalUnits
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ValidationPatterns::STRING_255,
            'email' => ValidationPatterns::UNIQUE_EMAIL,
            'password' => ValidationPatterns::password([Rules\Password::defaults()]),
            'organizational_unit_id' => ValidationPatterns::ALLOWED_ORGANIZATIONAL_UNIT_ID,
        ]);

        // Find the manager for the selected organizational unit
        $organizationalUnit = OrganizationalUnit::findOrFail($request->organizational_unit_id);
        $manager = User::where('organizational_unit_id', $organizationalUnit->id)
            ->where('role', User::ROLE_MANAGER)
            ->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'organizational_unit_id' => $request->organizational_unit_id,
            'manager_id' => $manager ? $manager->id : null,
            'role' => User::ROLE_EMPLOYEE,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return to_route('dashboard');
    }
}
