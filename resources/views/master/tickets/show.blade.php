@extends('layouts.app')
@section('title', 'Ticket #' . $ticket->id)

@section('content')
<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h3 class="mb-0">{{ $ticket->subject }} <span class="badge bg-info ms-2">{{ $ticket->status }}</span></h3>
        <small class="text-muted">#{{ $ticket->id }} &middot; {{ $ticket->tenant->name ?? '—' }} &middot; Opened by {{ $ticket->user->name ?? 'system' }} {{ $ticket->created_at->diffForHumans() }}</small>
    </div>
    <a href="{{ route('master.tickets.index') }}" class="btn btn-link">Back</a>
</div>

<div class="row g-3">
    <div class="col-md-8">
        @foreach($ticket->replies as $r)
            <div class="card mb-2 {{ $r->from_staff ? 'border-primary' : '' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <strong>{{ $r->user->name ?? 'system' }} {{ $r->from_staff ? '(staff)' : '' }}</strong>
                        <span>{{ $r->created_at->format('Y-m-d H:i') }}</span>
                    </div>
                    <div class="text-prewrap">{!! nl2br(e($r->message)) !!}</div>
                </div>
            </div>
        @endforeach

        <div class="card mt-3">
            <div class="card-header bg-white"><strong>Reply</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('master.tickets.reply', $ticket) }}">
                    @csrf
                    <textarea name="message" class="form-control" rows="4" required></textarea>
                    <div class="d-flex justify-content-between mt-3">
                        <select name="status" class="form-select w-auto">
                            <option value="">Keep status ({{ $ticket->status }})</option>
                            @foreach(['open', 'in_progress', 'waiting', 'resolved', 'closed'] as $s)
                                <option value="{{ $s }}">Set: {{ $s }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i>Send reply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white"><strong>Properties</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('master.tickets.update', $ticket) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select">
                            @foreach(['open', 'in_progress', 'waiting', 'resolved', 'closed'] as $s)
                                <option value="{{ $s }}" @selected($ticket->status === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Priority</label>
                        <select name="priority" class="form-select">
                            @foreach(['low', 'medium', 'high', 'urgent'] as $p)
                                <option value="{{ $p }}" @selected($ticket->priority === $p)>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Assign to</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">— unassigned —</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}" @selected($ticket->assigned_to === $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-outline-primary w-100"><i class="fas fa-save me-1"></i>Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
