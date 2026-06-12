<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\ApiKey;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ApiKeyController extends Controller
{
    public function index(): View
    {
        $keys = ApiKey::orderByDesc('id')->get();

        return view('master.api-keys.index', compact('keys'));
    }

    public function create(): View
    {
        return view('master.api-keys.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:191'],
            'scopes'  => ['nullable', 'string', 'max:500'],
            'ttl_days'=> ['nullable', 'integer', 'min:1', 'max:3650'],
        ]);

        $scopes = array_values(array_filter(array_map('trim', preg_split('/[\s,]+/', (string) $data['scopes']))));

        $result = ApiKey::generate($data['name'], $scopes, (int) ($data['ttl_days'] ?? 365));

        // Stash the one-time secret in the session for the show-after-create page
        session()->flash('new_api_secret', $result['secret']);
        session()->flash('new_api_key', $result['key']);

        return redirect()->route('master.api-keys.show', $result['id']);
    }

    public function show(ApiKey $apiKey): View
    {
        $apiKey->load('creator');
        $newSecret = session('new_api_secret');
        $newKey    = session('new_api_key');

        return view('master.api-keys.show', compact('apiKey', 'newSecret', 'newKey'));
    }

    public function destroy(ApiKey $apiKey): RedirectResponse
    {
        $apiKey->delete();
        return redirect()->route('master.api-keys.index')->with('success', 'API key revoked.');
    }
}
