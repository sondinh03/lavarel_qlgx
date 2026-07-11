@props([
    'label' => '',
    'placeholder' => '-- Chọn --',
    'options' => [],
    'wireModel' => '',
    'value' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
])

@php
    $wireAttr = $attributes->wire('model');
    $model = $wireAttr->value() ?? $wireModel;
    $currentValue = ($value !== null && $value !== '') ? (string) $value : '';
    $optionsList = collect($options)->map(fn ($text, $value) => [
        'value' => (string) $value,
        'label' => $text,
    ])->values()->all();

    $triggerClass = 'w-full h-11 px-4 pr-10 py-2.5 bg-white/80 backdrop-blur-sm border rounded-xl text-sm text-left
        shadow-mac-sm transition-all cursor-pointer
        focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 ' .
        ($error ? 'border-red-300' : 'border-black/[0.06]') .
        ($disabled ? ' opacity-50 cursor-not-allowed bg-slate-100/80' : '');
@endphp

<div {{ $attributes->whereDoesntStartWith('wire:model')->merge([
    'class' => 'w-full',
    'data-options' => json_encode($optionsList),
    'data-disabled' => $disabled ? '1' : '0',
    'data-value' => $currentValue,
]) }}
    x-data="{
        open: false,
        selectedValue: @js($currentValue),
        options: @js($optionsList),
        placeholder: @js($placeholder),
        disabled: @js($disabled),
        dropdownDirection: 'down',

        init() {
            this.syncFromDom();

            this._observer = new MutationObserver(() => this.syncFromDom());
            this._observer.observe(this.$el, {
                attributes: true,
                attributeFilter: ['data-options', 'data-disabled', 'data-value'],
            });

            if (typeof Livewire !== 'undefined') {
                Livewire.hook('message.processed', () => {
                    this.$nextTick(() => this.syncFromDom());
                });
            }
        },

        syncFromDom() {
            try {
                this.options = JSON.parse(this.$el.getAttribute('data-options') || '[]');
            } catch (e) {
                this.options = [];
            }

            this.disabled = this.$el.getAttribute('data-disabled') === '1';

            const fromData = this.$el.getAttribute('data-value');
            if (fromData !== null) {
                this.selectedValue = fromData;
            }

            if (this.$refs.hiddenInput && this.$refs.hiddenInput.value !== '') {
                this.selectedValue = this.$refs.hiddenInput.value;
            }
        },

        get selectedLabel() {
            if (this.selectedValue === '') return '';
            const found = this.options.find(o => o.value === String(this.selectedValue));
            return found ? found.label : '';
        },

        isSelected(option) {
            return String(option.value) === String(this.selectedValue);
        },

        toggle() {
            if (this.disabled) return;
            this.checkDirection();
            this.open = !this.open;
        },

        select(value) {
            this.selectedValue = value === null ? '' : String(value);
            this.open = false;

            if (this.$refs.hiddenInput) {
                this.$refs.hiddenInput.value = this.selectedValue;
                this.$refs.hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        },

        checkDirection() {
            const rect = this.$el.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;
            const dropdownHeight = Math.min(this.options.length * 40 + 16, 240);
            this.dropdownDirection = spaceBelow < dropdownHeight && spaceAbove > spaceBelow ? 'up' : 'down';
        },
    }"
    x-on:click.outside="open = false"
    x-on:keydown.escape.window="open = false">

    @if($label)
    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
        {{ $label }}
        @if($required)
        <span class="text-red-500 normal-case">*</span>
        @endif
    </label>
    @endif

    <div class="relative">
        <button
            type="button"
            x-on:click="toggle()"
            :disabled="disabled"
            :class="open ? 'ring-2 ring-primary-500/25 border-primary-300/40' : ''"
            class="{{ $triggerClass }} flex items-center justify-between gap-2"
            :aria-expanded="open.toString()"
            aria-haspopup="listbox">
            <span class="truncate" :class="selectedLabel ? 'text-slate-900' : 'text-slate-400'">
                <span x-text="selectedLabel || placeholder"></span>
            </span>
        </button>

        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
            <svg class="w-4 h-4 text-slate-400 transition-transform duration-150"
                :class="open ? 'rotate-180' : ''"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-[0.98]"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-[0.98]"
            :class="dropdownDirection === 'up' ? 'bottom-full mb-1.5' : 'top-full mt-1.5'"
            class="absolute z-50 w-full left-0 right-0
                   bg-white/90 backdrop-blur-xl rounded-xl
                   border border-black/[0.06] shadow-mac py-1.5 overflow-hidden"
            role="listbox">
            <ul class="max-h-60 overflow-y-auto py-0.5">
                @if($placeholder)
                <li>
                    <button
                        type="button"
                        x-on:click="select('')"
                        :class="selectedValue === ''
                            ? 'bg-primary-500/10 text-primary-700 font-medium'
                            : 'text-slate-700 hover:bg-black/[0.04]'"
                        class="w-full px-3 py-2 text-sm text-left flex items-center justify-between gap-2 mx-1 rounded-lg transition-colors"
                        style="width: calc(100% - 0.5rem);">
                        <span class="truncate">{{ $placeholder }}</span>
                        <svg x-show="selectedValue === ''" class="w-4 h-4 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </button>
                </li>
                @endif

                <template x-for="option in options" :key="option.value">
                    <li>
                        <button
                            type="button"
                            x-on:click="select(option.value)"
                            :class="isSelected(option)
                                ? 'bg-primary-500/10 text-primary-700 font-medium'
                                : 'text-slate-700 hover:bg-black/[0.04]'"
                            class="w-full px-3 py-2 text-sm text-left flex items-center justify-between gap-2 mx-1 rounded-lg transition-colors"
                            style="width: calc(100% - 0.5rem);">
                            <span class="truncate" x-text="option.label"></span>
                            <svg x-show="isSelected(option)" class="w-4 h-4 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                        </button>
                    </li>
                </template>
            </ul>
        </div>

        @if($model)
        <input
            type="hidden"
            x-ref="hiddenInput"
            value="{{ $currentValue }}"
            @if($wireAttr->value())
                {{ $wireAttr }}
            @else
                wire:model.defer="{{ $wireModel }}"
            @endif
            @if($required) required @endif>
        @endif
    </div>

    @if($error)
    <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @endif
</div>
