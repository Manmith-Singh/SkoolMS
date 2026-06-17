@php
    $name     = $name     ?? 'class_id';
    $classes  = $classes  ?? collect();
    $selected = (array) ($selected ?? request($name, []));

    $grouped = $classes->groupBy('name');

    $classSectionMap = [];
    foreach ($grouped as $className => $items) {
        foreach ($items as $item) {
            $keys = $item->section ? array_map('trim', explode(',', $item->section)) : ['_'];
            foreach ($keys as $key) {
                if ($key !== '') {
                    $classSectionMap[$className][$key][] = $item->id;
                }
            }
        }
    }

    $allSectionKeys = collect($classSectionMap)->flatMap(fn($sections) => array_keys($sections))->unique()->sort()->values();

    $selectedClassNames = $classes->whereIn('id', $selected)->pluck('name')->unique();
    $selectedSectionKeys = $classes->whereIn('id', $selected)
        ->pluck('section')
        ->flatMap(fn($s) => $s ? array_map('trim', explode(',', $s)) : ['_'])
        ->unique()
        ->filter();
@endphp

<div class="row g-2" id="csf-{{ Illuminate\Support\Str::random(6) }}" x-data="{
    selectedClasses: {{ Js::from($selectedClassNames->values()) }},
    selectedSections: {{ Js::from($selectedSectionKeys->values()) }},
    classOpen: false,
    sectionOpen: false,
    init() { this.sync(); },

    get availableSections() {
        const names = this.selectedClasses;
        const map = {{ Js::from($classSectionMap) }};
        const keys = new Set();
        names.forEach(n => { if (map[n]) { Object.keys(map[n]).forEach(k => keys.add(k)); } });
        return {{ Js::from($allSectionKeys) }}.filter(k => keys.has(k));
    },

    get computedClassIds() {
        const names = this.selectedClasses;
        const sections = this.selectedSections;
        const map = {{ Js::from($classSectionMap) }};
        const ids = new Set();
        names.forEach(n => {
            if (map[n]) {
                sections.forEach(s => {
                    if (map[n][s]) { map[n][s].forEach(id => ids.add(id)); }
                });
            }
        });
        return Array.from(ids);
    },

    sync() {
        const ids = this.computedClassIds;
        const container = document.getElementById('csf-inputs');
        container.innerHTML = '';
        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = '{{ $name }}[]';
            inp.value = id;
            container.appendChild(inp);
        });
    },

    toggleClass(name) {
        const idx = this.selectedClasses.indexOf(name);
        if (idx >= 0) { this.selectedClasses.splice(idx, 1); }
        else { this.selectedClasses.push(name); }
        this.selectedSections = this.availableSections.filter(s => this.selectedSections.includes(s));
        this.sync();
    },

    toggleSection(key) {
        const idx = this.selectedSections.indexOf(key);
        if (idx >= 0) { this.selectedSections.splice(idx, 1); }
        else { this.selectedSections.push(key); }
        this.sync();
    },

    selectAllClasses() {
        this.selectedClasses = {{ Js::from($grouped->keys()->values()) }};
        this.sync();
    },

    clearClasses() {
        this.selectedClasses = [];
        this.selectedSections = [];
        this.sync();
    },

    selectAllSections() {
        this.selectedSections = [...this.availableSections];
        this.sync();
    },

    clearSections() {
        this.selectedSections = [];
        this.sync();
    }
}">
    <div class="col-md-6">
        @unless($hideLabels ?? false)<label class="form-label">Classes *</label>@endunless
        <div class="csf-wrap" @click.away="classOpen = false">
            <button type="button" class="form-select text-start d-flex justify-content-between align-items-center" @click="classOpen = !classOpen" :aria-expanded="classOpen">
                <span class="text-truncate" x-text="selectedClasses.length ? selectedClasses.join(', ') : 'Select classes…'" :class="selectedClasses.length ? '' : 'text-muted'"></span>
                <i class="fas fa-chevron-down ms-2 text-muted small"></i>
            </button>
            <div class="csf-panel" x-show="classOpen" x-transition @click.away="classOpen = false">
                <div class="csf-panel-actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="selectAllClasses()">Select all</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="clearClasses()">Clear</button>
                </div>
                <template x-for="name in {{ Js::from($grouped->keys()->values()) }}" :key="name">
                    <label class="csf-option" :class="selectedClasses.includes(name) ? 'csf-selected' : ''">
                        <input type="checkbox" :checked="selectedClasses.includes(name)" @change="toggleClass(name)">
                        <span x-text="name"></span>
                    </label>
                </template>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        @unless($hideLabels ?? false)<label class="form-label">Sections *</label>@endunless
        <div class="csf-wrap" @click.away="sectionOpen = false">
            <button type="button" class="form-select text-start d-flex justify-content-between align-items-center" @click="sectionOpen = !sectionOpen" :disabled="!selectedClasses.length" :aria-expanded="sectionOpen">
                <span class="text-truncate" x-text="selectedSections.length ? selectedSections.join(', ') : 'Select sections…'" :class="selectedSections.length ? '' : 'text-muted'"></span>
                <i class="fas fa-chevron-down ms-2 text-muted small"></i>
            </button>
            <div class="csf-panel" x-show="sectionOpen" x-transition @click.away="sectionOpen = false">
                <div class="csf-panel-actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="selectAllSections()">Select all</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" @click="clearSections()">Clear</button>
                </div>
                <template x-for="key in availableSections" :key="key">
                    <label class="csf-option" :class="selectedSections.includes(key) ? 'csf-selected' : ''">
                        <input type="checkbox" :checked="selectedSections.includes(key)" @change="toggleSection(key)">
                        <span x-text="key === '_' ? 'Default' : 'Section ' + key"></span>
                    </label>
                </template>
                <p x-show="!availableSections.length" class="text-muted small px-2 py-2 mb-0">Select a class first.</p>
            </div>
        </div>
    </div>
    <div id="csf-inputs"></div>
</div>

<style>
.csf-wrap { position: relative; }
.csf-wrap .form-select[disabled] { background-color: #e9ecef; opacity: .65; }
.csf-panel {
    position: absolute; z-index: 1050; top: 100%; left: 0; right: 0; margin-top: 2px;
    background: #fff; border: 1px solid #dee2e6; border-radius: .375rem;
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15); max-height: 240px; overflow-y: auto;
}
.csf-panel-actions {
    display: flex; gap: .25rem; padding: .375rem .5rem;
    background: #f8f9fa; border-bottom: 1px solid #dee2e6;
    position: sticky; top: 0; z-index: 1;
}
.csf-option {
    display: flex; align-items: center; gap: .5rem;
    padding: .375rem .75rem; cursor: pointer; user-select: none; margin: 0;
}
.csf-option:hover { background: #f0f4ff; }
.csf-option.csf-selected { font-weight: 500; }
.csf-option input[type="checkbox"] { margin: 0; flex-shrink: 0; }
</style>


