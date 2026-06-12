@extends('layouts.app')
@section('title', 'System Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">System Settings</h3>
    <span class="badge bg-light text-dark">All changes are audit-logged</span>
</div>

<form method="POST" action="{{ route('master.settings.update') }}">
    @csrf
    @method('PUT')

    @foreach($settings as $group => $rows)
        <div class="card mb-3">
            <div class="card-header bg-white">
                <strong>{{ ucfirst($group) }}</strong>
                <small class="text-muted ms-2">{{ $rows->count() }} setting(s)</small>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($rows as $s)
                        <div class="col-md-6">
                            <label class="form-label">
                                <code>{{ $s->key }}</code>
                                <span class="text-muted small ms-1">({{ $s->type }})</span>
                            </label>
                            @if($s->type === 'boolean')
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="settings[{{ $s->key }}]" value="1" id="set-{{ $s->id }}" @checked((bool) $s->value)>
                                    <label class="form-check-label" for="set-{{ $s->id }}">{{ $s->value ? 'On' : 'Off' }}</label>
                                </div>
                            @elseif($s->type === 'integer')
                                <input type="number" name="settings[{{ $s->key }}]" value="{{ $s->value }}" class="form-control">
                            @else
                                <input type="text" name="settings[{{ $s->key }}]" value="{{ $s->value }}" class="form-control">
                            @endif
                            @if($s->description)
                                <div class="form-text">{{ $s->description }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-end mb-5">
        <button class="btn btn-primary"><i class="fas fa-save me-1"></i>Save all settings</button>
    </div>
</form>
@endsection
