@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('dashboard')],
    ['label' => 'Gia đình', 'url' => route('families.index')],
    ['label' => $isEdit ? 'Chỉnh sửa' : 'Thêm mới'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Header Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 bg-gradient-to-br from-primary-50 to-white">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">
                            {{ $isEdit ? 'Chỉnh sửa gia đình' : 'Thêm gia đình mới' }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $isEdit ? 'Cập nhật thông tin gia đình trong giáo xứ' : 'Tạo hồ sơ gia đình mới' }}
                        </p>
                    </div>

                    @if($isEdit && !$isLoading)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold w-fit
                        {{ $status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $status ? 'Hoạt động' : 'Không hoạt động' }}
                    </span>
                    @endif
                </div>
            </div>
        </div>

        @if($isLoading)
        {{-- Loading state --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12">
            <div class="flex items-center justify-center">
                <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin"></div>
            </div>
        </div>
        @else

        {{-- Error summary --}}
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-2xl p-5">
            <div class="flex items-start gap-3">
                <x-icon name="alert-circle" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-semibold text-red-800 mb-1">Vui lòng kiểm tra lại thông tin</p>
                    <ul class="text-sm text-red-700 space-y-0.5">
                        @foreach($errors->all() as $error)
                        <li>· {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        {{-- Form --}}
        <div class="space-y-6">

            {{-- Thông tin cơ bản --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-base font-semibold text-slate-900">Thông tin cơ bản</h2>
                </div>

                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                        <div class="lg:col-span-2">
                            <x-form-input
                                label="Tên gia đình"
                                name="name"
                                wire:model.defer="name"
                                placeholder="VD: Gia đình ông Nguyễn Văn A..."
                                required />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Giáo họ</label>
                            <select
                                wire:model.defer="parishGroupId"
                                class="w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm
                                       focus:outline-none focus:ring-2 focus:ring-primary-500
                                       @error('parishGroupId') border-red-400 @enderror">
                                <option value="">-- Chưa chọn giáo họ --</option>
                                @foreach($parishGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                            @error('parishGroupId')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Trạng thái</label>
                            <label class="flex items-center gap-3 px-4 py-3 rounded-xl border border-slate-200
                                          cursor-pointer hover:bg-slate-50 transition-all">
                                <input
                                    type="checkbox"
                                    wire:model.defer="status"
                                    class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                <div>
                                    <p class="text-sm font-medium text-slate-800">Gia đình đang hoạt động</p>
                                    <p class="text-xs text-slate-500">Hiển thị trong hệ thống quản lý</p>
                                </div>
                            </label>
                        </div>

                    </div>
                </div>
            </div>

            {{-- Thành viên gia đình --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">

                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-base font-semibold text-slate-900">Thành viên gia đình</h2>
                </div>

                <div class="p-6 space-y-6">

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        {{-- Người cha --}}
                        <div class="space-y-2 relative z-20">
                            <label class="block text-sm font-semibold text-slate-700">Người cha</label>

                            <x-searchable-select
                                wireModel="fatherId"
                                :options="$parishionerOptions"
                                placeholder="-- Chọn người cha --"
                                labelKey="name"
                                valueKey="id"
                                :value="$fatherId" />

                            @error('fatherId')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Người mẹ --}}
                        <div class="space-y-2 relative z-20">
                            <label class="block text-sm font-semibold text-slate-700">Người mẹ</label>

                            <x-searchable-select
                                wireModel="motherId"
                                :options="$parishionerOptions"
                                placeholder="-- Chọn người mẹ --"
                                labelKey="name"
                                valueKey="id"
                                :value="$motherId" />

                            @error('motherId')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- Con cái --}}
                    <div class="relative z-10 space-y-3">

                        <label class="block text-sm font-semibold text-slate-700">Con cái</label>

                        {{-- Select + Button --}}
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <x-searchable-select
                                    wireModel="selectedChildId"
                                    :options="$parishionerOptions"
                                    placeholder="-- Chọn con cái --"
                                    labelKey="name"
                                    valueKey="id"
                                    :value="$selectedChildId" />
                            </div>
                            <button
                                type="button"
                                wire:click="addChild"
                                class="px-4 py-2 rounded-xl bg-primary-500 text-white text-sm font-medium
                                       hover:bg-primary-600 active:scale-95 transition-all flex-shrink-0">
                                <x-icon name="plus" class="w-4 h-4" />
                            </button>
                        </div>

                        @error('childrenIds')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror

                        {{-- Danh sách con --}}
                        @if(count($childrenIds) > 0)
                        <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-slate-200">
                            @foreach($childrenIds as $childId)
                            @php
                            $child = collect($parishionerOptions)->firstWhere('id', $childId);
                            @endphp

                            @if($child)
                            <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-primary-50 border border-primary-100">
                                <span class="text-sm text-slate-700">{{ $child['name'] }}</span>
                                <button
                                    type="button"
                                    wire:click="removeChild({{ $childId }})"
                                    class="text-slate-400 hover:text-red-500 transition">
                                    <x-icon name="x" class="w-4 h-4" />
                                </button>
                            </div>
                            @endif
                            @endforeach
                        </div>
                        @endif

                    </div>

                </div>
            </div>

            {{-- Ghi chú --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-base font-semibold text-slate-900">Ghi chú</h2>
                </div>

                <div class="p-6">
                    <textarea
                        wire:model.defer="note"
                        rows="5"
                        placeholder="Thông tin thêm về gia đình..."
                        class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm resize-none
                               focus:outline-none focus:ring-2 focus:ring-primary-500
                               @error('note') border-red-400 @enderror"></textarea>
                    @error('note')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </div>

        {{-- Sticky action bar --}}
        <div class="sticky bottom-[calc(var(--bottom-offset)+12px)] z-30">
            <div class="bg-white/90 backdrop-blur rounded-2xl border border-slate-200 shadow-lg p-4">
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3">
                    <x-button wire:click="cancel" variant="secondary">
                        Hủy
                    </x-button>
                    <x-button wire:click="save" variant="primary" :loading="true">
                        <x-icon name="save" />
                        {{ $isEdit ? 'Lưu thay đổi' : 'Tạo gia đình' }}
                    </x-button>
                </div>
            </div>
        </div>

        @endif
    </div>
</div>

@push('page-title')
<span class="text-slate-800 font-semibold text-sm">
    {{ $isEdit ? 'Chỉnh sửa gia đình' : 'Thêm gia đình' }}
</span>
@endpush
