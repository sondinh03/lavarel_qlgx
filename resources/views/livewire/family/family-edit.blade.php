@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Gia đình', 'url' => route('families.index')],
    ['label' => $isEdit ? 'Chỉnh sửa' : 'Thêm mới'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="max-w-4xl mx-auto space-y-6">

        <x-mac-panel :overflow="true">
            <x-page-header
                :title="$isEdit ? 'Chỉnh sửa gia đình' : 'Thêm gia đình mới'"
                :description="$isEdit ? 'Cập nhật thông tin gia đình trong giáo xứ' : 'Tạo hồ sơ gia đình mới'"
                icon-type="default">
                @if($isEdit && !$isLoading)
                <x-slot name="actions">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                        {{ $status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                        {{ $status ? 'Hoạt động' : 'Không hoạt động' }}
                    </span>
                </x-slot>
                @endif
            </x-page-header>

        @if($isLoading)
            <div class="p-12">
                <div class="flex items-center justify-center">
                    <div class="w-8 h-8 border-4 border-primary-200 border-t-primary-500 rounded-full animate-spin"></div>
                </div>
            </div>
        @else

        {{-- Error summary --}}
        @if($errors->any())
        <div class="mx-4 lg:mx-6 mt-4 bg-red-50 border border-red-200 rounded-2xl p-5">
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

            <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-slate-50/70 overflow-x-auto">
                <div class="inline-flex rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    <button type="button" wire:click="switchTab('info')"
                        class="px-4 py-2.5 rounded-lg transition-all {{ $activeTab === 'info' ? 'bg-white shadow-sm text-primary-600 font-semibold' : 'text-slate-600' }}">
                        Thông tin hộ
                    </button>
                    <button type="button" wire:click="switchTab('members')"
                        class="px-4 py-2.5 rounded-lg transition-all {{ $activeTab === 'members' ? 'bg-white shadow-sm text-primary-600 font-semibold' : 'text-slate-600' }}">
                        Thành viên
                    </button>
                </div>
            </div>

        <div class="space-y-6 p-4 lg:p-6">

            @if($activeTab === 'info')
            <div class="space-y-5">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                        <div class="lg:col-span-2">
                            <x-form-input
                                label="Tên gia đình"
                                name="name"
                                wire:model.defer="name"
                                placeholder="Để trống sẽ tự đặt theo tên chủ hộ"
                                />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Giáo họ</label>
                            <x-searchable-select
                                wireModel="parishGroupId"
                                :options="$parishGroups"
                                placeholder="-- Chưa chọn giáo họ --"
                                labelKey="name"
                                valueKey="id"
                                :value="$parishGroupId" />
                            @error('parishGroupId')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Diện gia đình</label>
                            <input wire:model.defer="level" type="number" min="1" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm" />
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Địa chỉ</label>
                            <input wire:model.defer="address" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tỉnh/TP</label>
                            <input wire:model.defer="province" type="text" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm" />
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Trạng thái</label>
                            <label class="flex items-center gap-3 px-4 py-3 rounded-xl border border-slate-200 cursor-pointer">
                                <input type="checkbox" wire:model.defer="status" class="w-4 h-4 rounded text-primary-600">
                                <span class="text-sm text-slate-800">Gia đình đang hoạt động</span>
                            </label>
                        </div>

                        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer">
                                <input type="checkbox" wire:model.defer="isTransferred" class="rounded text-primary-600">
                                <span class="text-sm text-slate-700">Đã chuyển xứ</span>
                            </label>
                            <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 cursor-pointer">
                                <input type="checkbox" wire:model.defer="isIncludedInStats" class="rounded text-primary-600">
                                <span class="text-sm text-slate-700">Được thống kê</span>
                            </label>
                        </div>

                        <div class="lg:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Ghi chú</label>
                            <textarea wire:model.defer="note" rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm"></textarea>
                        </div>
                    </div>
            </div>
            @endif

            @if($activeTab === 'members')
            <div class="space-y-6">

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
                        <div class="flex flex-wrap gap-2 mt-3 pt-3 mac-hairline-t">
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
            @endif

        </div>

        {{-- Sticky action bar --}}
        <div class="sticky bottom-[calc(var(--bottom-offset)+12px)] z-30 px-4 lg:px-6 pb-4">
            <div class="bg-white/90 backdrop-blur rounded-2xl border border-black/[0.06] shadow-mac p-4">
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
        </x-mac-panel>
    </div>
</div>

@push('page-title')
<span class="text-slate-800 font-semibold text-sm">
    {{ $isEdit ? 'Chỉnh sửa gia đình' : 'Thêm gia đình' }}
</span>
@endpush
