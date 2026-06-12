@php
    $name     = $name     ?? 'class_id';
    $classes  = $classes  ?? collect();
    $selected = (array) ($selected ?? request($name, []));
    $required = (bool) ($required   ?? false);
    $size     = (int)  ($size       ?? 6);
    $wrapId   = 'msw_' . Illuminate\Support\Str::random(6);
@endphp
<div class="ms-wrap" id="{{ $wrapId }}" data-ms-wrap data-ms-size="{{ $size }}">
    <button type="button" class="form-select text-start d-flex justify-content-between align-items-center" data-ms-toggle aria-haspopup="listbox" aria-expanded="false">
        <span data-ms-summary class="text-truncate">Select classes…</span>
        <i class="fas fa-chevron-down ms-2 text-muted small"></i>
    </button>

    <div class="ms-panel" data-ms-panel hidden role="listbox" aria-multiselectable="true">
        <div class="ms-panel-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-ms-action="all">Select all</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-ms-action="none">Clear</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-ms-action="invert">Invert</button>
        </div>
        <ul class="ms-list">
            @foreach($classes as $c)
                @php $isSel = in_array($c->id, $selected); @endphp
                <li class="ms-item {{ $isSel ? 'ms-item-selected' : '' }}">
                    <label class="ms-option">
                        <input type="checkbox" name="{{ $name }}[]" value="{{ $c->id }}"
                               data-ms-checkbox {{ $isSel ? 'checked' : '' }}>
                        <span class="ms-text">{{ $c->display_name ?? $c->name }}</span>
                        <button type="button" class="ms-remove" data-ms-remove
                                aria-label="Remove {{ $c->display_name ?? $c->name }}">×</button>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>

    <small class="form-text text-muted ms-count" data-ms-count></small>
</div>

<style>
.ms-wrap { position: relative; }
.ms-wrap .form-select[data-ms-toggle] { cursor: pointer; background-color: #fff; }
.ms-wrap .form-select[data-ms-toggle]:hover { border-color: #86b7fe; }
.ms-panel {
    position: absolute;
    z-index: 1050;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 2px;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: .375rem;
    box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15);
    max-height: 280px;
    overflow: auto;
}
.ms-panel-actions {
    display: flex;
    gap: .25rem;
    padding: .375rem .5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    position: sticky;
    top: 0;
    z-index: 1;
}
.ms-list { list-style: none; padding: 0; margin: 0; }
.ms-item { padding: 0; }
.ms-option {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .375rem .75rem;
    cursor: pointer;
    user-select: none;
    margin: 0;
}
.ms-option:hover { background: #f0f4ff; }
.ms-option input[type="checkbox"] { margin: 0; flex-shrink: 0; }
.ms-text { flex: 1; min-width: 0; }
.ms-remove {
    display: none;
    background: transparent;
    border: 0;
    color: #dc3545;
    font-size: 1.25rem;
    line-height: 1;
    padding: 0 .35rem;
    cursor: pointer;
    border-radius: .25rem;
}
.ms-remove:hover { background: #f8d7da; }
.ms-item-selected .ms-remove { display: inline-block; }
.ms-item-selected .ms-text { font-weight: 500; }
.ms-count { margin-top: .25rem; }
</style>

@once
@push('scripts')
<script>
(function () {
    'use strict';

    function updateSummary(wrap) {
        const cbs     = wrap.querySelectorAll('[data-ms-checkbox]');
        const checked = wrap.querySelectorAll('[data-ms-checkbox]:checked');
        const summary = wrap.querySelector('[data-ms-summary]');
        const count   = wrap.querySelector('[data-ms-count]');
        if (summary) {
            if (checked.length === 0) {
                summary.textContent = 'Select classes…';
                summary.classList.add('text-muted');
            } else {
                const labels = Array.from(checked).map(cb => {
                    const opt = cb.closest('.ms-item');
                    return opt ? opt.querySelector('.ms-text').textContent.trim() : cb.value;
                });
                summary.textContent = labels.join(', ');
                summary.classList.remove('text-muted');
            }
        }
        if (count) {
            count.textContent = checked.length + ' of ' + cbs.length + ' selected';
        }
    }

    function refreshSelectionStyles(wrap) {
        wrap.querySelectorAll('[data-ms-checkbox]').forEach(cb => {
            const li = cb.closest('.ms-item');
            if (li) li.classList.toggle('ms-item-selected', cb.checked);
        });
    }

    function closeAllPanels(except) {
        document.querySelectorAll('[data-ms-panel]').forEach(p => {
            if (p !== except) {
                p.hidden = true;
                const wrap = p.closest('[data-ms-wrap]');
                if (wrap) {
                    const tog = wrap.querySelector('[data-ms-toggle]');
                    if (tog) tog.setAttribute('aria-expanded', 'false');
                }
            }
        });
    }

    // Click handler
    document.addEventListener('click', function (e) {
        // Toggle button
        const toggle = e.target.closest('[data-ms-toggle]');
        if (toggle) {
            e.preventDefault();
            e.stopPropagation();
            const wrap  = toggle.closest('[data-ms-wrap]');
            const panel = wrap.querySelector('[data-ms-panel]');
            const willOpen = panel.hidden;
            closeAllPanels(willOpen ? null : panel);
            panel.hidden = !willOpen;
            toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            return;
        }

        // Remove (×) button on a selected option
        const remove = e.target.closest('[data-ms-remove]');
        if (remove) {
            e.preventDefault();
            e.stopPropagation();
            const li = remove.closest('.ms-item');
            const cb = li && li.querySelector('[data-ms-checkbox]');
            if (cb) {
                cb.checked = false;
                li.classList.remove('ms-item-selected');
                const wrap = remove.closest('[data-ms-wrap]');
                if (wrap) updateSummary(wrap);
            }
            return;
        }

        // Action button (all/none/invert)
        const action = e.target.closest('[data-ms-action]');
        if (action) {
            e.preventDefault();
            e.stopPropagation();
            const wrap = action.closest('[data-ms-wrap]');
            const what = action.dataset.msAction;
            wrap.querySelectorAll('[data-ms-checkbox]').forEach(cb => {
                if (what === 'all')        cb.checked = true;
                else if (what === 'none')  cb.checked = false;
                else if (what === 'invert') cb.checked = !cb.checked;
            });
            refreshSelectionStyles(wrap);
            updateSummary(wrap);
            return;
        }

        // Click inside the panel but not on a control — ignore
        if (e.target.closest('[data-ms-panel]')) return;

        // Click outside any ms-wrap — close all panels
        if (!e.target.closest('[data-ms-wrap]')) {
            closeAllPanels(null);
        }
    });

    // Change handler for checkboxes
    document.addEventListener('change', function (e) {
        if (!e.target.matches('[data-ms-checkbox]')) return;
        const li   = e.target.closest('.ms-item');
        const wrap = e.target.closest('[data-ms-wrap]');
        if (li)   li.classList.toggle('ms-item-selected', e.target.checked);
        if (wrap) updateSummary(wrap);
    });

    // Initialize summaries on load
    function initAll() {
        document.querySelectorAll('[data-ms-wrap]').forEach(updateSummary);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
</script>
@endpush
@endonce
