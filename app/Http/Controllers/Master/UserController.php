<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\AuditLog;
use App\Models\Master\Tenant;
use App\Models\Master\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with('tenant')->orderByDesc('id');

        if ($q = trim((string) $request->get('q'))) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        if ($request->has('super_only')) {
            $query->whereNull('tenant_id');
        }

        $users = $query->paginate(25)->withQueryString();

        return view('master.users.index', compact('users'));
    }

    public function create(): View
    {
        $tenants = Tenant::orderBy('name')->get();
        $roles   = ['super_admin', 'admin', 'receptionist', 'teacher', 'student'];

        return view('master.users.create', compact('tenants', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:191'],
            'email'     => ['required', 'email', 'max:191', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', Rule::in(['super_admin', 'admin', 'receptionist', 'teacher', 'student'])],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
        ]);

        if ($data['role'] === 'super_admin') {
            $data['tenant_id'] = null;
        }

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'tenant_id' => $data['tenant_id'] ?? null,
        ]);

        AuditLog::record('user.created', $user, ['role' => $user->role]);

        return redirect()->route('master.users.index')->with('success', "User '{$user->name}' created.");
    }

    public function edit(User $user): View
    {
        $tenants = Tenant::orderBy('name')->get();
        $roles   = ['super_admin', 'admin', 'receptionist', 'teacher', 'student'];

        return view('master.users.edit', compact('user', 'tenants', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:191'],
            'email'     => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => ['nullable', 'string', 'min:8', 'confirmed'],
            'role'      => ['required', Rule::in(['super_admin', 'admin', 'receptionist', 'teacher', 'student'])],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
        ]);

        if ($data['role'] === 'super_admin') {
            $data['tenant_id'] = null;
        }

        $user->name  = $data['name'];
        $user->email = $data['email'];
        $user->role  = $data['role'];
        $user->tenant_id = $data['tenant_id'] ?? null;
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        AuditLog::record('user.updated', $user);

        return redirect()->route('master.users.index')->with('success', "User '{$user->name}' updated.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $name = $user->name;
        $user->delete();
        AuditLog::record('user.deleted', null, ['name' => $name]);

        return redirect()->route('master.users.index')->with('success', "User '{$name}' deleted.");
    }
}
