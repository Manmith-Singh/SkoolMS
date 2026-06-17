@php
    $name     = $name     ?? 'subject_id';
    $subjects = $subjects ?? collect();
    $selected = (array) ($selected ?? old($name, []));
@endphp
<div
    x-data="{
        open: false,
        selected: {{ Js::from($selected) }},
        toggle(id) {
            const idx = this.selected.indexOf(id);
            if (idx >= 0) { this.selected.splice(idx, 1); }
            else { this.selected.push(id); }
            this.sync();
        },
        selectAll() { this.selected = {{ Js::from($subjects->pluck('id')->values()) }}; this.sync(); },
        clear() { this.selected = []; this.sync(); },
        sync() {
            const container = document.getElementById('smi-inputs');
            container.innerHTML = '';
            this.selected.forEach(id => {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = '{{ $name }}[]';
                inp.value = id;
                container.appendChild(inp);
            });
        }
    }"
    class="csf-wrap"
>
    <label class="form-label">Subjects</label>
    <button type="button" class="form-select text-start d-flex justify-content-between align-items-center"
        @click="open = !open" :aria-expanded="open">
        <span class="text-truncate" x-text="selected.length ? selected.length + ' subject(s) selected' : 'Select subjects…'"
            :class="selected.length ? '' : 'text-muted'"></span>
        <i class="fas fa-chevron-down ms-2 text-muted small"></i>
    </button>
    <div class="csf-panel" x-show="open" x-transition @click.away="open = false">
        <div class="csf-panel-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="selectAll()">Select all</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="clear()">Clear</button>
        </div>
        <template x-for="s in {{ Js::from($subjects->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()) }}"
            :key="s.id">
            <label class="csf-option" :class="selected.includes(s.id) ? 'csf-selected' : ''">
                <input type="checkbox" :checked="selected.includes(s.id)" @change="toggle(s.id)">
                <span x-text="s.name"></span>
            </label>
        </template>
    </div>
    <div id="smi-inputs"></div>
</div>
