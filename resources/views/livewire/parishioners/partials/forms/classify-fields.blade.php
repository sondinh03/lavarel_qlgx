@php
$labels = [
    'ethnic' => 'Dân tộc',
    'career' => 'Nghề nghiệp',
    'education_level' => 'Học vấn',
    'specialist_level' => 'Trình độ chuyên môn',
    'catechism_level' => 'Trình độ giáo lý',
    'position' => 'Chức vụ',
    'language' => 'Ngôn ngữ',
    'holy_order_status' => 'Thánh chức',
];
$configKeys = [
    'ethnic' => 'parishioner.ethnic',
    'career' => 'parishioner.career',
    'education_level' => 'parishioner.education_level',
    'specialist_level' => 'parishioner.specialist_level',
    'catechism_level' => 'parishioner.catechism_level',
    'position' => 'parishioner.position',
    'language' => 'parishioner.language',
    'holy_order_status' => 'parishioner.holy_order_status',
];
$input = "w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500";
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($labels as $field => $label)
        @include('livewire.parishioners.partials.field-select', [
            'label' => $label,
            'wire' => $field,
            'options' => config($configKeys[$field], []),
            'nullable' => true,
        ])
    @endforeach
    <div class="md:col-span-2 lg:col-span-3">
        <label class="block text-sm font-semibold text-slate-700 mb-1">Chuyên ngành giáo lý</label>
        <input wire:model.defer="catechism_major" type="text" class="{{ $input }}" />
    </div>
</div>
