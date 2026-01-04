{{-- resources/views/components/modal.blade.php --}}
@props([
    'show' => false,
    'title' => '',
    'description' => '',
    'maxWidth' => 'xl', // sm, md, lg, xl, 2xl
    'scrollable' => true,
])

@if($show)
<div
    class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
    role="dialog"
    aria-modal="true"
    {{ $attributes->merge(['wire:click' => '$set(\'showForm\', false)']) }}>

    <div
        class="bg-white rounded-2xl shadow-xl w-full max-w-{{ $maxWidth }} 
               {{ $scrollable ? 'max-h-[90vh] overflow-hidden flex flex-col' : '' }}"
        wire:click.stop>

        {{-- Header --}}
        <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
            <h2 class="text-xl font-bold text-slate-900">{{ $title }}</h2>
            @if($description)
            <p class="text-sm text-slate-600 mt-1">{{ $description }}</p>
            @endif
        </div>

        {{-- Body --}}
        <div class="{{ $scrollable ? 'flex-1 overflow-y-auto' : '' }} p-6">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @if(isset($footer))
        <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50">
            {{ $footer }}
        </div>
        @endif
    </div>
</div>
@endif