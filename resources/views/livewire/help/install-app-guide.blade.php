@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Trợ giúp'],
    ['label' => 'Cài đặt lên điện thoại'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{ platform: 'ios' }">
    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-3xl space-y-5">

        <x-mac-panel :overflow="true">
            <x-page-header
                title="Cài đặt lên điện thoại"
                description="Thêm website vào màn hình chính để mở nhanh như một ứng dụng — không cần App Store hay Google Play."
                icon-type="default" />
        </x-mac-panel>

        <x-mac-panel class="p-4 lg:p-6 space-y-3">
            <x-inline-tip tone="amber">
                <p class="font-semibold text-sm mb-1">iPhone / iPad: bắt buộc dùng Safari</p>
                <p class="text-amber-800/90">
                    Trên iOS, chỉ <strong>Safari</strong> mới có chức năng
                    <strong>«Thêm vào Màn hình chính»</strong>.
                    Chrome, Edge, Firefox… trên iPhone <strong>không hỗ trợ</strong> bước này — hãy mở đúng địa chỉ site bằng Safari rồi làm theo hướng dẫn bên dưới.
                </p>
            </x-inline-tip>
            <x-inline-tip>
                Sau khi thêm, biểu tượng <strong>MVGX</strong> (Mục vụ Giáo xứ) xuất hiện trên màn hình chính.
                Chạm vào để mở toàn màn hình, không cần mở trình duyệt mỗi lần.
            </x-inline-tip>
        </x-mac-panel>

        {{-- Chọn nền tảng --}}
        <x-mac-panel class="p-4 lg:p-6">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Bạn đang dùng thiết bị nào?</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button type="button"
                    @click="platform = 'ios'"
                    class="text-left rounded-2xl border px-4 py-3.5 transition shadow-mac-sm"
                    :class="platform === 'ios'
                        ? 'border-primary-300 bg-primary-50/80 ring-1 ring-primary-200'
                        : 'border-black/[0.06] bg-white/60 hover:bg-white'">
                    <p class="text-sm font-semibold text-slate-900">iPhone / iPad</p>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Dùng Safari → Chia sẻ → Thêm vào Màn hình chính
                    </p>
                </button>
                <button type="button"
                    @click="platform = 'android'"
                    class="text-left rounded-2xl border px-4 py-3.5 transition shadow-mac-sm"
                    :class="platform === 'android'
                        ? 'border-primary-300 bg-primary-50/80 ring-1 ring-primary-200'
                        : 'border-black/[0.06] bg-white/60 hover:bg-white'">
                    <p class="text-sm font-semibold text-slate-900">Android</p>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Chrome (hoặc trình duyệt tương tự) → Cài đặt ứng dụng / Thêm vào màn hình chính
                    </p>
                </button>
            </div>
        </x-mac-panel>

        {{-- ═══════════════ iOS ═══════════════ --}}
        <div x-show="platform === 'ios'" x-cloak class="space-y-5">

            <x-mac-panel class="p-4 lg:p-6">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Luồng iPhone / iPad</p>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-1 text-sm">
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Bước 1</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Mở bằng Safari</p>
                    </div>
                    <div class="hidden sm:flex text-slate-300 px-1">→</div>
                    <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Bước 2</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Chạm Chia sẻ</p>
                    </div>
                    <div class="hidden sm:flex text-slate-300 px-1">→</div>
                    <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Bước 3</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Thêm màn hình chính</p>
                    </div>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">1</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Mở trang bằng Safari</h2>
                        <p class="text-xs text-slate-500">Không dùng Chrome / Edge / Firefox trên iOS</p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Mở ứng dụng <strong>Safari</strong> trên iPhone hoặc iPad.</li>
                        <li>Gõ địa chỉ website (hoặc mở từ bookmark / tin nhắn) và đăng nhập như bình thường.</li>
                        <li>Đảm bảo thanh địa chỉ đang là Safari — nếu đang ở trình duyệt khác, <strong>sao chép link</strong> rồi dán vào Safari.</li>
                    </ol>
                    <x-inline-tip tone="amber">
                        Nếu không thấy mục «Thêm vào Màn hình chính» ở bước sau, gần như chắc bạn đang không mở bằng Safari.
                    </x-inline-tip>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">2</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Mở menu Chia sẻ</h2>
                        <p class="text-xs text-slate-500">Nút hình hộp có mũi tên hướng lên</p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Chạm nút <strong>Chia sẻ</strong>
                            <span class="inline-flex align-middle mx-0.5 text-primary-600" aria-hidden="true">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8M16 6l-4-4-4 4M12 2v13" />
                                </svg>
                            </span>
                            (thường nằm ở thanh dưới hoặc cạnh thanh địa chỉ).
                        </li>
                        <li>Vuốt danh sách tùy chọn xuống nếu cần để tìm thêm mục.</li>
                    </ol>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">3</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Thêm vào Màn hình chính</h2>
                        <p class="text-xs text-slate-500">Tạo biểu tượng như app</p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Chọn <strong>Thêm vào Màn hình chính</strong> (Add to Home Screen).</li>
                        <li>Đặt tên ngắn nếu muốn (mặc định thường là <strong>MVGX</strong> hoặc tên site).</li>
                        <li>Chạm <strong>Thêm</strong> góc trên bên phải.</li>
                        <li>Quay lại màn hình chính — chạm biểu tượng mới để mở.</li>
                    </ol>
                </div>
            </x-mac-panel>
        </div>

        {{-- ═══════════════ Android ═══════════════ --}}
        <div x-show="platform === 'android'" x-cloak class="space-y-5">

            <x-mac-panel class="p-4 lg:p-6">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Luồng Android</p>
                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-1 text-sm">
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Bước 1</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Mở bằng Chrome</p>
                    </div>
                    <div class="hidden sm:flex text-slate-300 px-1">→</div>
                    <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Bước 2</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Menu ⋮</p>
                    </div>
                    <div class="hidden sm:flex text-slate-300 px-1">→</div>
                    <div class="sm:hidden text-center text-slate-300 text-xs">↓</div>
                    <div class="flex-1 rounded-xl bg-primary-50 border border-primary-100 px-3 py-2.5 text-center">
                        <p class="text-[11px] text-primary-600 font-medium">Bước 3</p>
                        <p class="font-semibold text-primary-900 text-xs sm:text-sm">Cài / Thêm</p>
                    </div>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">1</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Mở trang bằng Chrome</h2>
                        <p class="text-xs text-slate-500">Khuyến nghị Google Chrome</p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Mở <strong>Chrome</strong> (hoặc trình duyệt hỗ trợ PWA tương tự).</li>
                        <li>Truy cập website và đăng nhập.</li>
                    </ol>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">2</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Mở menu trình duyệt</h2>
                        <p class="text-xs text-slate-500">Ba chấm dọc ⋮ góc trên</p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Chạm nút <strong>⋮</strong> (menu) ở góc trên bên phải.</li>
                        <li>Tìm một trong các mục:
                            <ul class="mt-2 ml-5 list-disc space-y-1 text-slate-600">
                                <li><strong>Cài đặt ứng dụng</strong> / <strong>Install app</strong></li>
                                <li>hoặc <strong>Thêm vào màn hình chính</strong> / <strong>Add to Home screen</strong></li>
                            </ul>
                        </li>
                    </ol>
                    <x-inline-tip>
                        Một số máy hiện banner «Cài đặt ứng dụng» ngay phía trên trang — có thể chạm luôn thay vì vào menu.
                    </x-inline-tip>
                </div>
            </x-mac-panel>

            <x-mac-panel :overflow="true">
                <div class="px-4 lg:px-6 py-4 mac-hairline-b bg-white/40 flex items-center gap-3">
                    <span class="w-8 h-8 rounded-full bg-primary-600 text-white text-sm font-bold flex items-center justify-center flex-shrink-0">3</span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Xác nhận cài đặt</h2>
                        <p class="text-xs text-slate-500">Biểu tượng xuất hiện trên màn hình chính</p>
                    </div>
                </div>
                <div class="p-4 lg:p-6 space-y-3 text-sm text-slate-700 leading-relaxed">
                    <ol class="list-decimal list-inside space-y-2">
                        <li>Xác nhận <strong>Cài đặt</strong> / <strong>Thêm</strong>.</li>
                        <li>Mở biểu tượng từ màn hình chính hoặc ngăn kéo ứng dụng.</li>
                    </ol>
                </div>
            </x-mac-panel>
        </div>

        {{-- FAQ --}}
        <x-mac-panel class="p-4 lg:p-6 space-y-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">Câu hỏi thường gặp</p>
            <div class="space-y-4 text-sm text-slate-700">
                <div>
                    <p class="font-semibold text-slate-900">Tại sao iPhone không thấy «Thêm vào Màn hình chính»?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Hãy mở lại bằng <strong>Safari</strong>. Các trình duyệt khác trên iOS không cung cấp chức năng này.
                        Nếu vẫn không thấy: trong menu Chia sẻ, vuốt ngang hàng biểu tượng hoặc vuốt xuống danh sách rồi chọn
                        <strong>Chỉnh sửa thao tác…</strong> để bật mục đó.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Đây có phải app trên App Store / Play Store không?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Không. Đây là website được «ghim» lên màn hình chính (PWA). Vẫn cần mạng để dùng đầy đủ chức năng.
                    </p>
                </div>
                <div>
                    <p class="font-semibold text-slate-900">Xóa biểu tượng có mất dữ liệu không?</p>
                    <p class="mt-1 text-slate-600 leading-relaxed">
                        Không. Chỉ bỏ lối tắt trên máy; tài khoản và dữ liệu trên hệ thống vẫn giữ nguyên. Bạn có thể thêm lại bất kỳ lúc nào.
                    </p>
                </div>
            </div>
        </x-mac-panel>

    </div>
</div>
