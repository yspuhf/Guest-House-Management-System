<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['email' => 'This account has been deactivated. Please contact the administrator.']);
            }

            $this->auditService->log($user, 'User Logged In', 'Auth', User::class, $user->id);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function quickLogin(Request $request)
    {
        $role = $request->input('role');
        $user = User::where('primary_role', $role)->first();
        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->route('dashboard')->with('success', "Logged in as {$user->name} ({$user->primary_role})");
        }
        return back()->with('error', 'Demo user not found.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $this->auditService->log($user, 'User Logged Out', 'Auth', User::class, $user->id);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been successfully logged out.');
    }

    public function profile()
    {
        return view('auth.profile', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];
        $user->designation = $validated['designation'];
        $user->department = $validated['department'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Your profile details have been successfully updated.');
    }
}

