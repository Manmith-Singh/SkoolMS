<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\AuditLog;
use App\Models\Master\SupportTicket;
use App\Models\Master\SupportTicketReply;
use App\Models\Master\Tenant;
use App\Models\Master\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportTicketController extends Controller
{
    public function index(Request $request): View
    {
        $query = SupportTicket::with(['tenant', 'user', 'assignee'])->orderByDesc('id');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }
        if ($tenantId = $request->get('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        $tickets = $query->paginate(25)->withQueryString();
        $tenants = Tenant::orderBy('name')->get();

        return view('master.tickets.index', compact('tickets', 'tenants'));
    }

    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['tenant', 'user', 'assignee', 'replies.user']);
        $staff = User::where('role', 'super_admin')->orderBy('name')->get();

        return view('master.tickets.show', compact('ticket', 'staff'));
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'message'  => ['required', 'string', 'max:5000'],
            'status'   => ['nullable', 'in:open,in_progress,waiting,resolved,closed'],
        ]);

        SupportTicketReply::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => auth()->id(),
            'from_staff' => true,
            'message'    => $data['message'],
        ]);

        $ticket->update([
            'last_reply_at' => now(),
            'status'        => $data['status'] ?? $ticket->status,
            'closed_at'     => ($data['status'] ?? null) === 'closed' ? now() : $ticket->closed_at,
        ]);

        AuditLog::record('ticket.replied', $ticket);

        return back()->with('success', 'Reply posted.');
    }

    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $data = $request->validate([
            'status'     => ['required', 'in:open,in_progress,waiting,resolved,closed'],
            'priority'   => ['required', 'in:low,medium,high,urgent'],
            'assigned_to'=> ['nullable', 'integer', 'exists:users,id'],
        ]);

        $ticket->update($data + [
            'closed_at' => $data['status'] === 'closed' ? ($ticket->closed_at ?? now()) : null,
        ]);

        AuditLog::record('ticket.updated', $ticket, $data);

        return back()->with('success', 'Ticket updated.');
    }

    public function create(): View
    {
        $tenants = Tenant::orderBy('name')->get();

        return view('master.tickets.create', compact('tenants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject'   => ['required', 'string', 'max:191'],
            'message'   => ['required', 'string', 'max:5000'],
            'priority'  => ['required', 'in:low,medium,high,urgent'],
            'category'  => ['nullable', 'string', 'max:100'],
            'tenant_id' => ['nullable', 'integer', 'exists:tenants,id'],
        ]);

        $ticket = SupportTicket::create([
            'subject'  => $data['subject'],
            'priority' => $data['priority'],
            'category' => $data['category'] ?? null,
            'tenant_id'=> $data['tenant_id'] ?? null,
            'user_id'  => auth()->id(),
            'status'   => 'open',
        ]);

        SupportTicketReply::create([
            'ticket_id'  => $ticket->id,
            'user_id'    => auth()->id(),
            'from_staff' => true,
            'message'    => $data['message'],
        ]);

        AuditLog::record('ticket.created', $ticket);

        return redirect()->route('master.tickets.show', $ticket)->with('success', 'Ticket created.');
    }
}
