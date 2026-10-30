<?php

namespace App\Http\Controllers;

use App\Models\OrganizationUnit;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = User::with(['organizationUnit', 'roles']);

        if ($request->filled('role')) {
            $query->where('primary_role', $request->role);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('employee_id', 'like', "%{$request->search}%");
            });
        }

        $users = $query->paginate(20)->withQueryString();
        $roles = Role::all();

        return view('masters.users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'employee_id' => 'nullable|string|max:50|unique:users,employee_id',
            'organization_unit_id' => 'nullable|exists:organization_units,id',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'primary_role' => 'required|string|exists:roles,name',
            'password' => 'required|min:8',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        $role = Role::where('name', $validated['primary_role'])->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }

        $this->auditService->log(Auth::user(), 'Created User Account', 'RBAC', User::class, $user->id, null, ['name' => $user->name, 'role' => $user->primary_role]);

        return back()->with('success', "User '{$user->name}' created successfully.");
    }

    public function toggleActive(User $user)
    {
        $before = ['is_active' => $user->is_active];
        $user->is_active = !$user->is_active;
        $user->save();

        $this->auditService->log(Auth::user(), 'Toggled User Status', 'RBAC', User::class, $user->id, $before, ['is_active' => $user->is_active]);

        return back()->with('success', "User account status updated.");
    }
}

