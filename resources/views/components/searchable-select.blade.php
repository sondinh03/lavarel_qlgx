{{-- resources/views/components/searchable-select.blade.php --}}
@props([
'wireModel' => '',
'options' => [],
'placeholder' => 'Chọn...',
'labelKey' => 'name',
'valueKey' => 'id',
'value'     => null,
'live'      => false,
])

<div {{ $attributes->merge(['class' => 'relative']) }}
    x-data="{
        open: false,
        search: '',
        selectedLabel: '',
        selectedValue: '',
        options: [],
        wireModel: @js($wireModel),
        valueKey: @js($valueKey),
        labelKey: @js($labelKey),
        live: @js($live),

        init() {
            this.syncFromDom();

            this._observer = new MutationObserver(() => this.syncFromDom());
            this._observer.observe(this.$el, {
                attributes: true,
                attributeFilter: ['data-options', 'data-value'],
            });
        },

        syncFromDom() {
            try {
                this.options = JSON.parse(this.$el.getAttribute('data-options') || '[]');
            } catch (e) {
                this.options = [];
            }

            const current = this.$el.getAttribute('data-value') ?? '';
            this.selectedValue = current === '' ? '' : String(current);

            if (this.selectedValue) {
                const found = this.options.find(o => String(o[this.valueKey]) === this.selectedValue);
                this.selectedLabel = found ? found[this.labelKey] : this.selectedLabel;
            } else if (!this.live) {
                this.selectedLabel = '';
            }
        },

        get filtered() {
            if (!this.search) return this.options;
            return this.options.filter(o =>
                String(o[this.labelKey] ?? '').toLowerCase().includes(this.search.toLowerCase())
            );
        },

        isSelected(option) {
            return String(option[this.valueKey]) === String(this.selectedValue ?? '');
        },

        select(value, label) {
            const normalized = value === '' || value === null ? null : value;
            this.selectedLabel = label;
            this.selectedValue = normalized === null ? '' : String(normalized);
            this.open = false;
            this.search = '';

            if (this.live) {
                $wire.set(this.wireModel, normalized);
                return;
            }

            $dispatch('select-option', { model: this.wireModel, value: value, label: label });
        },

        clear() {
            this.selectedLabel = '';
            this.selectedValue = '';
            this.search = '';

            if (this.live) {
                $wire.set(this.wireModel, null);
                return;
            }

            $dispatch('select-option', { model: this.wireModel, value: '', label: '' });
        },

        dropdownDirection: 'down',

        checkDirection() {
            const rect = this.$el.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            const dropdownHeight = 280;
            this.dropdownDirection = spaceBelow < dropdownHeight && spaceAbove > spaceBelow
                ? 'up'
                : 'down';
        },
    }"
    data-options='@json($options)'
    data-value="{{ $value }}"
    x-on:click.outside="open = false; search = ''"
    x-on:select-option.window="
        if ($event.detail.model === wireModel) {
            selectedLabel = $event.detail.label;
            selectedValue = $event.detail.value === '' ? '' : String($event.detail.value);
            open = false;
            search = '';
        }
    ">

    <button type="button"
        x-on:click="checkDirection(); open = !open"
        :class="open ? 'ring-2 ring-primary-500 border-transparent' : 'border-slate-200'"
        class="w-full px-3 py-2 rounded-xl border bg-white text-sm text-left
               flex items-center justify-between gap-2 transition-all focus:outline-none">

        <span :class="selectedLabel ? 'text-slate-900' : 'text-slate-400'">
            <span x-text="selectedLabel || '{{ $placeholder }}'"></span>
        </span>

        <div class="flex items-center gap-1 flex-shrink-0">
            <span x-show="selectedLabel"
                x-on:click.stop="clear()"
                class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
            </span>
            <svg class="w-4 h-4 text-slate-400 transition-transform"
                :class="open ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    <div x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-cloak
        :class="dropdownDirection === 'up'
            ? 'bottom-full mb-1'
            : 'top-full mt-1'"
        class="absolute z-50 w-full bg-white rounded-xl border border-slate-200 shadow-lg overflow-hidden">

        <div class="p-2 border-b border-slate-100">
            <input type="text"
                x-model="search"
                x-ref="searchInput"
                x-init="$watch('open', val => val && $nextTick(() => $refs.searchInput.focus()))"
                placeholder="Tìm kiếm..."
                class="w-full px-3 py-1.5 text-sm bg-slate-50 border border-slate-300
                       rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>

        <ul class="max-h-52 overflow-y-auto py-1">
            <template x-for="option in filtered" :key="option[valueKey]">
                <li x-on:click="select(option[valueKey], option[labelKey])"
                    :class="isSelected(option)
                        ? 'bg-primary-50 text-primary-700 font-semibold'
                        : 'text-slate-700 hover:bg-slate-50'"
                    class="px-3 py-2 text-sm cursor-pointer transition-colors">
                    <span x-text="option[labelKey]"></span>
                </li>
            </template>

            <li x-show="filtered.length === 0"
                class="px-3 py-4 text-sm text-slate-400 text-center italic">
                Không tìm thấy kết quả
            </li>
        </ul>
    </div>

    @unless($live)
    <input type="hidden"
        x-ref="hiddenInput"
        wire:model.defer="{{ $wireModel }}"
        x-on:select-option.window="
        if ($event.detail.model === wireModel) {
            $el.value = $event.detail.value;
            $el.dispatchEvent(new Event('input'));
        }
    ">
    @endunless
</div>
