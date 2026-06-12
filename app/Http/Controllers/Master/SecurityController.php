<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\AuditLog;
use App\Models\Master\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::orderBy('name')->get();
        $roles = ['super_admin', 'admin', 'receptionist', 'teacher', 'student'];

        // Permission catalogue (purely declarative; ties into audit log meta)
        $permissions = [
            'tenants'    => ['view', 'create', 'update', 'suspend', 'delete'],
            'users'      => ['view', 'create', 'update', 'delete', 'impersonate'],
            'plans'      => ['view', 'create', 'update', 'delete'],
            'invoices'   => ['view', 'create', 'update', 'delete', 'refund'],
            'payments'   => ['view', 'record', 'refund'],
            'settings'   => ['view', 'update'],
            'api_keys'   => ['view', 'create', 'revoke'],
            'reports'    => ['view', 'export'],
            'tickets'    => ['view', 'reply', 'assign', 'close'],
            'audit'      => ['view', 'export'],
        ];

        return view('master.security.index', compact('users', 'roles', 'permissions'));
    }

    public function updateRole(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:super_admin,admin,receptionist,teacher,student'],
        ]);

        $user->update(['role' => $data['role'], 'tenant_id' => $data['role'] === 'super_admin' ? null : $user->tenant_id]);
        AuditLog::record('user.role_changed', $user, ['new_role' => $user->role]);

        return back()->with('success', "Role for '{$user->name}' updated to {$user->role}.");
    }
}
