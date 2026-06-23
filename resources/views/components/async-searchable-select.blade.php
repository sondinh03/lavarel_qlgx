{{-- resources/views/components/async-searchable-select.blade.php --}}
@props([
    'wireModel' => '',
    'searchMethod' => '',
    'searchParams' => [],
    'placeholder' => 'Chọn...',
    'labelKey' => 'name',
    'valueKey' => 'id',
    'value' => null,
    'disabled' => false,
    'disabledTooltip' => 'Vui lòng chọn cấp trên trước',
    'initialLabel' => '',
])

<div {{ $attributes->merge(['class' => 'relative']) }}
    x-data="{
        open: false,
        search: '',
        options: [],
        loading: false,
        selectedLabel: @js($initialLabel),
        selectedValue: @js($value !== null && $value !== '' ? (string) $value : ''),
        wireModel: @js($wireModel),
        searchMethod: @js($searchMethod),
        searchParams: @js($searchParams),
        valueKey: @js($valueKey),
        labelKey: @js($labelKey),
        disabled: @js((bool) $disabled),

        init() {
            if (this.selectedValue && this.selectedLabel) {
                this.options = [{ [this.valueKey]: this.selectedValue, [this.labelKey]: this.selectedLabel }];
            }
        },

        async fetchOptions() {
            if (this.disabled || !this.searchMethod) return;
            this.loading = true;
            try {
                const result = await $wire.call(this.searchMethod, this.search, ...this.searchParams);
                this.options = Array.isArray(result) ? result : [];
            } catch (e) {
                this.options = [];
            } finally {
                this.loading = false;
            }
        },

        openDropdown() {
            if (this.disabled) return;
            this.checkDirection();
            this.open = !this.open;
            if (this.open) {
                this.fetchOptions();
                this.$nextTick(() => this.$refs.searchInput?.focus());
            } else {
                this.search = '';
            }
        },

        get filtered() {
            if (!this.search) return this.options;
            const q = this.search.toLowerCase();
            return this.options.filter(o =>
                String(o[this.labelKey] ?? '').toLowerCase().includes(q)
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
            $wire.set(this.wireModel, normalized);
        },

        clear() {
            this.selectedLabel = '';
            this.selectedValue = '';
            this.search = '';
            this.options = [];
            $wire.set(this.wireModel, null);
        },

        dropdownDirection: 'down',

        checkDirection() {
            const rect = this.$el.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            const dropdownHeight = 280;
            this.dropdownDirection = spaceBelow < dropdownHeight && spaceAbove > spaceBelow ? 'up' : 'down';
        },
    }"
    x-on:click.outside="open = false; search = ''"
    x-effect="
        disabled = @js((bool) $disabled);
        if (selectedValue && !selectedLabel) {
            const found = options.find(o => String(o[valueKey]) === String(selectedValue));
            if (found) selectedLabel = found[labelKey];
        }
    ">

    <button type="button"
        x-on:click="openDropdown()"
        :disabled="disabled"
        :title="disabled ? '{{ $disabledTooltip }}' : ''"
        :class="[
            disabled ? 'opacity-60 cursor-not-allowed bg-slate-50' : 'bg-white cursor-pointer',
            open ? 'ring-2 ring-primary-500 border-transparent' : 'border-slate-200'
        ]"
        class="w-full px-3 py-2 rounded-xl border text-sm text-left flex items-center justify-between gap-2 transition-all focus:outline-none">

        <span :class="selectedLabel ? 'text-slate-900' : 'text-slate-400'">
            <span x-text="selectedLabel || '{{ $placeholder }}'"></span>
        </span>

        <div class="flex items-center gap-1 flex-shrink-0">
            <span x-show="selectedLabel && !disabled"
                x-on:click.stop="clear()"
                class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </span>
            <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </button>

    <div x-show="open && !disabled"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-cloak
        :class="dropdownDirection === 'up' ? 'bottom-full mb-1' : 'top-full mt-1'"
        class="absolute z-50 w-full bg-white rounded-xl border border-slate-200 shadow-lg overflow-hidden">

        <div class="p-2 border-b border-slate-100">
            <input type="text"
                x-model="search"
                x-ref="searchInput"
                x-on:input.debounce.300ms="fetchOptions()"
                placeholder="Tìm kiếm..."
                class="w-full px-3 py-1.5 text-sm bg-slate-50 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
        </div>

        <div x-show="loading" class="px-3 py-4 text-sm text-slate-400 text-center">
            <span class="inline-flex items-center gap-2">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Đang tải...
            </span>
        </div>

        <ul x-show="!loading" class="max-h-52 overflow-y-auto py-1">
            <template x-for="option in filtered" :key="option[valueKey]">
                <li x-on:click="select(option[valueKey], option[labelKey])"
                    :class="isSelected(option) ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-700 hover:bg-slate-50'"
                    class="px-3 py-2 text-sm cursor-pointer transition-colors">
                    <span x-text="option[labelKey]"></span>
                </li>
            </template>
            <li x-show="filtered.length === 0"
                class="px-3 py-4 text-sm text-slate-400 text-center italic">
                Không có kết quả
            </li>
        </ul>
    </div>
</div>
