<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Models\Pasien;
use App\Support\CodeGenerator;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        DB::transaction(function () use ($request, &$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->assignRole('pasien');

            // Generate no rekam medis (default: non-member)
            $isMember = false;
            $prefix = $isMember ? 'RM' : 'NM';
            
            $noRekamMedis = CodeGenerator::prefixNumber(
                new Pasien(),
                'no_rekam_medis',
                $prefix,
                6,
                '-'
            );

            // Create pasien profile
            Pasien::create([
                'user_id' => $user->id,
                'no_rekam_medis' => $noRekamMedis,
                'is_member' => $isMember,
                'member_since' => null,
            ]);

            event(new Registered($user));
        });

        Auth::login($user);

        return redirect(route('landing', absolute: false));
    }
}
