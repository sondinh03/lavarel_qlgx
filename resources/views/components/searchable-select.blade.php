{{-- resources/views/components/searchable-select.blade.php --}}
@props([
'wireModel' => '',
'options' => [],
'placeholder' => 'Chọn...',
'labelKey' => 'name',
'valueKey' => 'id',
'value'     => null,
])

<div x-data="{
        open: false,
        search: '',
        selectedLabel: '',
        options: [],

        init() {
            this.options = JSON.parse(this.$el.dataset.options);

            const current = this.$el.dataset.value;
            if (current) {
                const found = this.options.find(o => String(o.{{ $valueKey }}) === String(current));
                if (found) this.selectedLabel = found.{{ $labelKey }};
            }
        },

        get filtered() {
            if (!this.search) return this.options;
            return this.options.filter(o =>
                o.{{ $labelKey }}.toLowerCase().includes(this.search.toLowerCase())
            );
        },

        select(value, label) {
            $dispatch('select-option', { model: '{{ $wireModel }}', value: value, label: label });
        },

        clear() {
            $dispatch('select-option', { 
                model: '{{ $wireModel }}', 
                value: '', 
                label: '' 
            });
            this.selectedLabel = '';
            this.search = '';
        },

        dropdownDirection: 'down',

        checkDirection() {
            const rect = this.$el.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            const dropdownHeight = 280; // max-h-52 ~ 208px + search bar
            this.dropdownDirection = spaceBelow < dropdownHeight && spaceAbove > spaceBelow 
                ? 'up' 
                : 'down';
        },
    }"
    data-options="{{ json_encode($options) }}"
    data-value="{{ $value }}" 
    x-on:click.outside="open = false; search = ''"
    x-on:select-option.window="
        if ($event.detail.model === '{{ $wireModel }}') {
            selectedLabel = $event.detail.label;
            open = false;
            search = '';
        }
    "
    class="relative">

    {{-- Trigger --}}
    <button type="button"
        x-on:click="checkDirection(); open = !open"
        :class="open ? 'ring-2 ring-primary-500 border-transparent' : 'border-slate-200'"
        class="w-full px-3 py-2 rounded-xl border bg-white text-sm text-left
               flex items-center justify-between gap-2 transition-all focus:outline-none">

        <span :class="selectedLabel ? 'text-slate-900' : 'text-slate-400'">
            <span x-text="selectedLabel || '{{ $placeholder }}'"></span>
        </span>

        <div class="flex items-center gap-1 flex-shrink-0">
            {{-- Clear --}}
            <span x-show="selectedLabel"
                x-on:click.stop="clear()"
                class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </span>
            {{-- Chevron --}}
            <svg class="w-4 h-4 text-slate-400 transition-transform"
                :class="open ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-cloak
        :class="dropdownDirection === 'up' 
            ? 'bottom-full mb-1' 
            : 'top-full mt-1'"
        class="absolute z-50 w-full bg-white rounded-xl border border-slate-200 shadow-lg overflow-hidden">

        {{-- Search --}}
        <div class="p-2 border-b border-slate-100">
            <input type="text"
                x-model="search"
                x-ref="searchInput"
                x-init="$watch('open', val => val && $nextTick(() => $refs.searchInput.focus()))"
                placeholder="Tìm kiếm..."
                class="w-full px-3 py-1.5 text-sm bg-slate-50 border border-slate-300
                       rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>

        {{-- Options --}}
        <ul class="max-h-52 overflow-y-auto py-1">
            <template x-for="option in filtered" :key="option.{{ $valueKey }}">
                <li x-on:click="select(option.{{ $valueKey }}, option.{{ $labelKey }})"
                    :class="String(option.{{ $valueKey }}) === String($wire.get('{{ $wireModel }}'))
                        ? 'bg-primary-50 text-primary-700 font-semibold'
                        : 'text-slate-700 hover:bg-slate-50'"
                    class="px-3 py-2 text-sm cursor-pointer transition-colors">
                    <span x-text="option.{{ $labelKey }}"></span>
                </li>
            </template>

            {{-- Empty --}}
            <li x-show="filtered.length === 0"
                class="px-3 py-4 text-sm text-slate-400 text-center italic">
                Không tìm thấy kết quả
            </li>
        </ul>
    </div>

    {{-- Hidden input để @error hoạt động --}}
    <input type="hidden"
        x-ref="hiddenInput"
        wire:model.defer="{{ $wireModel }}"
        x-on:select-option.window="
        if ($event.detail.model === '{{ $wireModel }}') {
            $el.value = $event.detail.value;
            $el.dispatchEvent(new Event('input'));
        }
    ">
</div>