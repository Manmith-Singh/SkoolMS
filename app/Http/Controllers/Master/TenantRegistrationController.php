<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantRegistrationController extends Controller
{
    public function showForm(): View
    {
        return view('master.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:191'],
            'subdomain'        => ['required', 'string', 'max:63', 'regex:/^[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?$/', 'unique:tenants,subdomain'],
            'db_name'          => ['required', 'string', 'max:64', 'unique:tenants,db_name'],
            'db_user'          => ['nullable', 'string', 'max:64'],
            'db_password'      => ['nullable', 'string', 'max:255'],
            'admin_name'       => ['required', 'string', 'max:191'],
            'admin_email'      => ['required', 'email', 'max:191', 'unique:users,email'],
            'admin_password'   => ['required', 'string', 'min:8', 'confirmed'],
            'contact_phone'    => ['nullable', 'string', 'max:30'],
            'address'          => ['nullable', 'string', 'max:500'],
        ]);

        $exit = Artisan::call('tenant:create', [
            'name'             => $data['name'],
            'subdomain'        => $data['subdomain'],
            '--db-name'        => $data['db_name'],
            '--db-user'        => $data['db_user'] ?? null,
            '--db-password'    => $data['db_password'] ?? null,
            '--admin-email'    => $data['admin_email'],
            '--admin-name'     => $data['admin_name'],
            '--admin-password' => $data['admin_password'],
        ]);

        if ($exit !== 0) {
            return back()->withInput()->withErrors(['subdomain' => 'Provisioning failed. Check the details and try again.']);
        }

        $tenant = Tenant::where('subdomain', $data['subdomain'])->firstOrFail();

        $tenant->update([
            'contact_email' => $data['admin_email'],
            'contact_phone' => $data['contact_phone'] ?? null,
            'address'       => $data['address'] ?? null,
        ]);

        return redirect()
            ->route('master.register.success', ['subdomain' => $tenant->subdomain])
            ->with('registered_tenant', $tenant)
            ->with('registered_password', $data['admin_password']);
    }

    public function success(string $subdomain): View
    {
        $tenant = Tenant::where('subdomain', $subdomain)->firstOrFail();
        $password = session('registered_password');

        return view('master.register-success', [
            'tenant'   => $tenant,
            'password' => $password,
        ]);
    }

    public function dashboard(): View
    {
        $tenants = Tenant::orderByDesc('id')->limit(20)->get();
        $stats = [
            'tenants' => Tenant::count(),
            'active'  => Tenant::where('status', 'active')->count(),
        ];

        return view('master.dashboard', compact('tenants', 'stats'));
    }
}
