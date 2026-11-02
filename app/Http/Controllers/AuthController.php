<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showSetup()
    {
        if (User::query()->exists()) {
            return redirect()->route('login');
        }

        return view('auth', ['mode' => 'setup']);
    }

    public function setup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => 'required|string|min:10|max:72|confirmed',
        ]);
        if (strlen($validated['password']) > 72) {
            throw ValidationException::withMessages([
                'password' => ['The password must not exceed 72 bytes.'],
            ]);
        }

        $user = DB::transaction(function () use ($validated) {
            $setup = DB::table('workspace_settings')
                ->where('key', 'setup_completed')
                ->lockForUpdate()
                ->first();

            if (! $setup || $setup->value !== '0' || User::query()->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['The first administrator account has already been created.'],
                ]);
            }

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'admin',
                'is_active' => true,
            ]);

            DB::table('workspace_settings')
                ->where('key', 'setup_completed')
                ->update(['value' => '1', 'updated_at' => now()]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/');
    }

    public function showLogin()
    {
        if (! User::query()->exists()) {
            return redirect()->route('setup');
        }

        return view('auth', ['mode' => 'login']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember')) || ! Auth::user()->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
