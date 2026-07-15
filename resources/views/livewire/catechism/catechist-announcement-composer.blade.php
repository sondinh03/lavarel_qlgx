@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Thông báo GLV'],
]" />
@endsection

@php
    $input = 'w-full px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
        focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all border-black/[0.06]';
    $label = 'block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase';
@endphp

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-2xl space-y-4">
        <x-mac-panel :overflow="true">
            <x-page-header
                icon-type="default"
                title="Thông báo tới giáo lý viên"
                description="Gửi thông tin từ ban giáo lý — toàn trường hoặc theo khối.">
            </x-page-header>

            <form wire:submit.prevent="send" class="p-4 lg:p-6 space-y-5">
                @if($errors->any())
                <div class="p-4 bg-red-50/90 border border-red-200/80 rounded-xl shadow-mac-sm">
                    <ul class="text-sm text-red-700 space-y-0.5">
                        @foreach($errors->all() as $error)
                        <li>· {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div>
                    <label class="{{ $label }}">Đối tượng nhận <span class="text-red-500 normal-case">*</span></label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-all
                            {{ $audience === 'all'
                                ? 'border-primary-300/60 bg-primary-50/80 shadow-mac-sm'
                                : 'border-black/[0.06] bg-white/60 hover:bg-white/80' }}">
                            <input type="radio" wire:model="audience" value="all"
                                class="mt-0.5 text-primary-600 focus:ring-primary-500/30">
                            <span>
                                <span class="block text-sm font-semibold text-slate-800">Toàn trường</span>
                                <span class="block text-xs text-slate-500 mt-0.5">Tất cả tài khoản GLV trong giáo xứ</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border cursor-pointer transition-all
                            {{ $audience === 'grade'
                                ? 'border-primary-300/60 bg-primary-50/80 shadow-mac-sm'
                                : 'border-black/[0.06] bg-white/60 hover:bg-white/80' }}">
                            <input type="radio" wire:model="audience" value="grade"
                                class="mt-0.5 text-primary-600 focus:ring-primary-500/30">
                            <span>
                                <span class="block text-sm font-semibold text-slate-800">Theo khối</span>
                                <span class="block text-xs text-slate-500 mt-0.5">GLV đang phụ trách lớp thuộc khối</span>
                            </span>
                        </label>
                    </div>
                </div>

                @if($audience === 'grade')
                <div>
                    <label class="{{ $label }}">Khối <span class="text-red-500 normal-case">*</span></label>
                    <select wire:model="gradeLevelId" class="{{ $input }} h-11">
                        <option value="">— Chọn khối —</option>
                        @foreach($gradeOptions as $opt)
                        <option value="{{ $opt['id'] }}">{{ $opt['name'] }}</option>
                        @endforeach
                    </select>
                    @error('gradeLevelId')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <div class="rounded-xl border border-black/[0.06] bg-slate-50/80 px-4 py-3 text-sm text-slate-600">
                    Dự kiến gửi tới
                    <span class="font-semibold text-slate-900">{{ number_format($recipientPreview) }}</span>
                    tài khoản
                    @if($audience === 'grade')
                    (có gắn lớp thuộc khối, năm học hiện tại).
                    @else
                    (vai trò giáo lý viên).
                    @endif
                </div>

                <div>
                    <label class="{{ $label }}">Tiêu đề <span class="text-red-500 normal-case">*</span></label>
                    <input type="text" wire:model.defer="title" class="{{ $input }} h-11"
                        maxlength="150" placeholder="VD: Họp ban giáo lý tuần này">
                    @error('title')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="{{ $label }}">Nội dung <span class="text-red-500 normal-case">*</span></label>
                    <textarea wire:model.defer="body" rows="5" class="{{ $input }}"
                        maxlength="2000"
                        placeholder="Nội dung thông báo gửi tới giáo lý viên…"></textarea>
                    @error('body')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="{{ $label }}">Liên kết (tuỳ chọn)</label>
                    <input type="text" wire:model.defer="linkUrl" class="{{ $input }} h-11"
                        placeholder="/diem-danh hoặc https://…">
                    <p class="mt-1 text-xs text-slate-400">Khi GLV mở thông báo sẽ chuyển tới liên kết này.</p>
                    @error('linkUrl')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end gap-3 pt-1">
                    <x-button type="submit" variant="primary"
                        wire:loading.attr="disabled"
                        wire:target="send">
                        <span wire:loading.remove wire:target="send">Gửi thông báo</span>
                        <span wire:loading wire:target="send">Đang gửi…</span>
                    </x-button>
                </div>
            </form>
        </x-mac-panel>
    </div>
</div>
