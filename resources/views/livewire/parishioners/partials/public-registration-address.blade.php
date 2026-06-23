@php $input = "w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
    @include('livewire.parishioners.partials.forms.address-fields')

    <div class="grid grid-cols-1 gap-4 pt-2">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Dân tộc</label>
            <select wire:model.defer="ethnic" class="{{ $input }}">
                <option value="">-- Chọn --</option>
                @foreach(config('parishioner.ethnic', []) as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Nghề nghiệp</label>
            <select wire:model.defer="career" class="{{ $input }}">
                <option value="">-- Chọn --</option>
                @foreach(config('parishioner.career', []) as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">Trình độ học vấn</label>
            <select wire:model.defer="education_level" class="{{ $input }}">
                <option value="">-- Chọn --</option>
                @foreach(config('parishioner.education_level', []) as $k => $v)
                <option value="{{ $k }}">{{ $v }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
