@section('title', 'Thông tin giáo xứ')

<div class="relative min-h-[calc(100vh-8rem)] py-4 sm:py-6 px-3 sm:px-4 lg:px-6">
    <div class="mx-auto max-w-2xl space-y-5">
        <x-mac-panel :overflow="true">
            <x-page-header
                icon-type="default"
                title="Thông tin giáo xứ"
                description="Cập nhật thông tin giáo xứ và cách chốt điểm danh tự động.">
            </x-page-header>

            <form wire:submit.prevent="save" class="p-4 lg:p-6 space-y-4">
                @if($errors->any())
                <div class="p-4 bg-red-50/90 border border-red-200/80 rounded-xl shadow-mac-sm">
                    <ul class="text-sm text-red-700 space-y-0.5">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div wire:key="catechism-logo-{{ $currentLogoPath ?? 'none' }}">
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Logo ban giáo lý
                        </label>
                        <x-avatar-upload
                            wireModel="logo"
                            :existing="$currentLogoPath"
                            inputId="catechism_logo_upload"
                            removeMethod="removeLogo"
                            sizeClass="w-28 h-28" />
                        <p class="mt-1.5 text-xs text-slate-400">Hiển thị trên thẻ học sinh / GLV.</p>
                        <div wire:loading wire:target="logo" class="mt-1.5 text-xs text-primary-600">Đang tải ảnh lên...</div>
                    </div>

                    <div wire:key="parish-logo-{{ $currentParishLogoPath ?? 'none' }}">
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Logo giáo xứ
                        </label>
                        <x-avatar-upload
                            wireModel="parishLogo"
                            :existing="$currentParishLogoPath"
                            inputId="parish_logo_upload"
                            removeMethod="removeParishLogo"
                            sizeClass="w-28 h-28" />
                        <p class="mt-1.5 text-xs text-slate-400">Logo chính thức của giáo xứ.</p>
                        <div wire:loading wire:target="parishLogo" class="mt-1.5 text-xs text-primary-600">Đang tải ảnh lên...</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Tên giáo xứ <span class="text-red-500 normal-case">*</span>
                        </label>
                        <input type="text" wire:model.defer="name"
                            class="w-full h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                {{ $errors->has('name') ? 'border-red-300 bg-red-50/80' : 'border-black/[0.06]' }}" />
                        @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Mã giáo xứ
                        </label>
                        <input type="text" value="{{ $code }}" readonly
                            class="w-full h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] text-sm
                                bg-slate-50/80 text-slate-500 shadow-mac-sm cursor-not-allowed" />
                        <p class="mt-1 text-xs text-slate-400">Mã giáo xứ không thể thay đổi.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Điện thoại
                        </label>
                        <input type="text" wire:model.defer="phone"
                            class="w-full h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                border-black/[0.06]" />
                        @error('phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Cha xứ
                        </label>
                        <input type="text" wire:model.defer="parish_priest_name"
                            class="w-full h-11 px-4 py-2.5 rounded-xl border text-sm bg-white/80 backdrop-blur-sm shadow-mac-sm
                                focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all
                                border-black/[0.06]" />
                        @error('parish_priest_name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Giáo phận <span class="text-red-500 normal-case">*</span>
                        </label>
                        <x-searchable-select
                            wire:key="diocese-{{ $dioceseId ?? 'none' }}"
                            wireModel="dioceseId"
                            :options="$dioceseOptions"
                            :live="true"
                            placeholder="— Chọn giáo phận —"
                            labelKey="name"
                            valueKey="id"
                            :value="$dioceseId" />
                        @error('dioceseId')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Giáo hạt <span class="text-red-500 normal-case">*</span>
                        </label>
                        <x-searchable-select
                            wire:key="deanery-{{ $dioceseId ?? 'none' }}-{{ $deaneryId ?? 'none' }}"
                            wireModel="deaneryId"
                            :options="$deaneryOptions"
                            :live="true"
                            placeholder="{{ $dioceseId ? '— Chọn giáo hạt —' : 'Chọn giáo phận trước' }}"
                            labelKey="name"
                            valueKey="id"
                            :value="$deaneryId" />
                        @error('deaneryId')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Tỉnh / Thành phố
                        </label>
                        <x-searchable-select
                            wire:key="province-{{ $province ?? 'none' }}"
                            wireModel="province"
                            :options="$provinceOptions"
                            :live="true"
                            placeholder="— Chọn tỉnh/thành —"
                            labelKey="name"
                            valueKey="id"
                            :value="$province" />
                        @error('province')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Xã / Phường
                        </label>
                        <x-searchable-select
                            wire:key="ward-{{ $province ?? 'none' }}-{{ $ward ?? 'none' }}"
                            wireModel="ward"
                            :options="$wardOptions"
                            :live="true"
                            placeholder="{{ $province ? '— Chọn xã/phường —' : 'Chọn tỉnh/thành trước' }}"
                            labelKey="name"
                            valueKey="id"
                            :value="$ward" />
                        @error('ward')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Giờ chốt số liệu điểm danh --}}
                <div class="rounded-xl border border-black/[0.06] bg-white/60 p-4 shadow-mac-sm space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Điểm danh</p>
                            <p class="mt-1 text-sm font-semibold text-slate-800">Tự động kết luận sau giờ chốt</p>
                        </div>
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none flex-shrink-0">
                            <input type="checkbox" wire:model="attendanceAutoFinalizeEnabled" class="mac-checkbox">
                            <span class="text-sm font-semibold text-slate-700">Bật</span>
                        </label>
                    </div>

                    <div class="text-sm text-slate-600 leading-relaxed space-y-2 rounded-xl bg-slate-50/80 p-3">
                        <p class="font-semibold text-slate-700">Quy tắc kết luận vắng:</p>
                        <ul class="list-disc ml-5 space-y-1">
                            <li>Buổi phải có ít nhất một người được điểm danh bằng QR hoặc điểm tay.</li>
                            <li>Sau giờ chốt, học sinh chưa có bản ghi được <em>coi là vắng không phép</em> khi xem báo cáo, xuất Excel và tính điểm chuyên cần.</li>
                            <li><strong>Khóa buổi</strong> kết luận ngay theo quy tắc trên và không cho quét/sửa thêm; có thể mở lại để sửa.</li>
                        </ul>
                        <p>
                            Hệ thống không tạo thêm bản ghi vắng và không tự khóa buổi. Trước giờ chốt,
                            học sinh chưa có bản ghi vẫn hiển thị là <strong>chưa điểm danh (?)</strong>.
                        </p>
                        <p class="text-xs text-slate-500">
                            Buổi chưa có ai điểm danh không bị kết luận vắng cả lớp, tránh trừ oan khi buổi chưa diễn ra
                            hoặc giáo lý viên quên điểm danh.
                        </p>
                    </div>

                    <div class="{{ $attendanceAutoFinalizeEnabled ? '' : 'opacity-50 pointer-events-none' }}">
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                            Giờ chốt số liệu
                        </label>
                        <input type="time" wire:model.defer="attendanceAutoFinalizeTime"
                            class="w-full sm:w-40 h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] text-sm
                                bg-white/80 backdrop-blur-sm shadow-mac-sm tabular-nums
                                focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                        <p class="mt-1.5 text-xs text-slate-400">
                            Mặc định 20:00 (giờ Việt Nam). Nên đặt sau khi lớp cuối cùng trong ngày thường điểm danh xong.
                        </p>
                        @error('attendanceAutoFinalizeTime')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="logo,parishLogo,save"
                        class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl
                            bg-primary-500 text-white text-sm font-semibold shadow-mac-sm
                            hover:bg-primary-600 disabled:opacity-60
                            focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/40
                            active:scale-[0.98] transition-all">
                        <span wire:loading.remove wire:target="logo,parishLogo,save">Lưu thay đổi</span>
                        <span wire:loading wire:target="logo,parishLogo,save">Đang lưu...</span>
                    </button>
                </div>
            </form>
        </x-mac-panel>
    </div>
</div>
