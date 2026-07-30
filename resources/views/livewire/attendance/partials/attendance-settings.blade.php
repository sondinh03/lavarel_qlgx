{{-- Tab: Cài đặt điểm danh cấp giáo xứ --}}
<div class="p-4 lg:p-6">
    <div class="mx-auto max-w-2xl space-y-4">

        <div class="rounded-2xl border border-black/[0.06] bg-white/70 backdrop-blur-sm shadow-mac-sm overflow-hidden">
            <div class="px-5 py-4 mac-hairline-b bg-white/40">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Áp dụng cho toàn giáo xứ</p>
                <h2 class="mt-1 text-base font-semibold tracking-tight text-slate-900">
                    Giờ chốt số liệu điểm danh
                </h2>
            </div>

            <div class="p-5 space-y-4">
                <div class="rounded-xl bg-slate-50/80 ring-1 ring-black/[0.04] p-4 text-sm text-slate-600 leading-relaxed space-y-2">
                    <p class="font-semibold text-slate-700">Cách hoạt động</p>
                    <ul class="list-disc ml-5 space-y-1">
                        <li>Buổi phải có <strong>ít nhất một em được điểm danh</strong> (quét QR hoặc đánh tay).</li>
                        <li>
                            Sau giờ chốt của <strong>ngày buổi đó</strong> (hoặc khi buổi được khóa), những em
                            <strong>chưa được điểm danh</strong> sẽ được tính là <strong>KP</strong> trên lưới,
                            Excel và điểm chuyên cần — hệ thống không ghi bản ghi vào database.
                        </li>
                        <li>
                            Chỉ buổi <strong>chưa khóa</strong> và <strong>chưa tới giờ chốt</strong> mới hiện
                            <strong>chưa điểm danh</strong>.
                        </li>
                        <li>Điểm danh bù sau đó (buổi còn mở) thì số liệu tự đúng lại, không cần làm thêm gì.</li>
                    </ul>
                    <p class="text-xs text-slate-500">
                        Buổi chưa ai điểm danh sẽ không bị tính vắng cả lớp, tránh trừ oan khi buổi chưa diễn ra
                        hoặc giáo lý viên quên điểm danh. Lớp nghỉ hẳn thì nên <strong>hủy buổi</strong>.
                        Xem chi tiết tại hướng dẫn bên dưới.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1.5 tracking-wide uppercase">
                        Giờ chốt số liệu
                    </label>
                    <input type="time" wire:model.defer="autoFinalizeTime"
                        class="w-full sm:w-40 h-11 px-4 py-2.5 rounded-xl border border-black/[0.06] text-sm
                            bg-white/80 backdrop-blur-sm shadow-mac-sm tabular-nums
                            focus:outline-none focus:ring-2 focus:ring-primary-500/25 focus:border-primary-300/40 transition-all" />
                    <p class="mt-1.5 text-xs text-slate-400">
                        Mặc định 20:00. Nên đặt muộn hơn buổi cuối cùng trong ngày của giáo xứ.
                    </p>
                    @error('autoFinalizeTime')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-xl bg-primary-50/60 ring-1 ring-primary-100/70 p-4 text-sm text-slate-600 leading-relaxed">
                    <p class="font-semibold text-slate-800">Cần số liệu sớm hơn giờ chốt?</p>
                    <p class="mt-1">
                        Bấm <strong>Khóa</strong> ở phiên đã điểm danh xong: buổi đó chốt ngay, không cần đợi tới giờ.
                        Muốn sửa lại thì bấm <strong>Mở lại</strong>.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-1">
                    <a href="{{ route('help.attendance') }}"
                        class="text-sm font-semibold text-primary-600 hover:text-primary-700">
                        Xem hướng dẫn điểm danh →
                    </a>
                    <x-button wire:click="saveAttendanceSettings" wire:loading.attr="disabled"
                        wire:target="saveAttendanceSettings" variant="primary">
                        <span wire:loading.remove wire:target="saveAttendanceSettings">Lưu cài đặt</span>
                        <span wire:loading wire:target="saveAttendanceSettings">Đang lưu...</span>
                    </x-button>
                </div>
            </div>
        </div>

    </div>
</div>
