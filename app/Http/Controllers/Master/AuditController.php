<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with(['user', 'tenant'])->orderByDesc('id');

        if ($action = $request->get('action')) {
            $query->where('action', 'like', "%{$action}%");
        }
        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }
        if ($tenantId = $request->get('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('master.audit.index', compact('logs'));
    }
}
