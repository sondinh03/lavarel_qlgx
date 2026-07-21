@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Giáo lý viên', 'url' => route('catechists.index')],
    ['label' => $teacher['full_name_with_saint'] ?? 'Chi tiết'],
]" />
@endsection

<div class="min-h-screen bg-apple-gray p-2 sm:p-4 lg:p-6"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <a href="#teacher-profile-main" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="teacher-profile-main" class="mx-auto max-w-7xl">
        <x-mac-panel :overflow="true">

            <div class="p-4 lg:p-6 mac-hairline-b bg-white/40">
                <div class="flex flex-col sm:flex-row gap-4 sm:items-start justify-between">
                    <div class="flex items-start gap-4 flex-1 min-w-0">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-700
                            text-white flex items-center justify-center text-2xl font-semibold
                            shadow-mac-sm ring-4 ring-primary-50/80 flex-shrink-0">
                            {{ mb_substr($teacher['full_name'] ?? 'G', 0, 1, 'UTF-8') }}
                        </div>

                        <div class="flex-1 min-w-0">
                            <h1 class="text-[22px] font-semibold tracking-tight text-slate-900 truncate">
                                {{ $teacher['full_name_with_saint'] ?? '' }}
                            </h1>
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-sm mt-1.5">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold {{ $teacher['status_badge_class'] ?? '' }}">
                                    {{ $teacher['status_label'] ?? '' }}
                                </span>

                                @if(!empty($teacher['parish_group']))
                                <span class="hidden sm:inline text-black/10">|</span>
                                <span class="text-slate-500">
                                    Giáo họ:
                                    <span class="font-semibold text-slate-900">{{ $teacher['parish_group'] }}</span>
                                </span>
                                @endif

                                @if(!empty($teacher['has_account']))
                                <span class="hidden sm:inline text-black/10">|</span>
                                <span class="text-slate-500">Có tài khoản đăng nhập</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 flex-shrink-0">
                        <x-button wire:click="edit" variant="primary">
                            <x-icon name="edit" />
                            Chỉnh sửa
                        </x-button>
                        <x-button variant="danger"
                            confirm="Xóa giáo lý viên này sẽ xóa luôn tài khoản đăng nhập. Bạn chắc chắn?"
                            wire="deleteTeacher">
                            <x-icon name="trash" />
                            Xóa
                        </x-button>
                    </div>
                </div>
            </div>

            <div class="p-4 lg:p-6">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8">

                    <div class="lg:col-span-3 space-y-6">
                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-1 px-1">
                                Thông tin cá nhân
                            </h2>
                            <div class="divide-y divide-black/[0.04] rounded-xl bg-white/40 border border-black/[0.04]">
                                <x-info-row label="Tên thánh" :value="$teacher['saint_name'] ?? ''" />
                                <x-info-row label="Họ và tên" :value="$teacher['full_name'] ?? ''" />
                                <x-info-row label="Ngày sinh" :value="$teacher['birthday'] ?? ''" />
                                <x-info-row label="Giới tính" :value="$teacher['gender_label'] ?? ''" />
                            </div>
                        </section>

                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-1 px-1">
                                Liên hệ
                            </h2>
                            <div class="divide-y divide-black/[0.04] rounded-xl bg-white/40 border border-black/[0.04]">
                                <x-info-row label="Điện thoại" :value="$teacher['phone_number'] ?? ''" />
                                <x-info-row label="Email" :value="$teacher['email'] ?? ''" />
                                <x-info-row label="Địa chỉ" :value="$teacher['address'] ?? ''" />
                                <x-info-row label="Giáo họ" :value="$teacher['parish_group'] ?? ''" />
                            </div>
                        </section>
                    </div>

                    <div class="lg:col-span-2 space-y-6">
                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-2 px-1">
                                Lớp đang phụ trách
                                @if(!empty($teacher['classes']))
                                <span class="font-normal normal-case tracking-normal text-slate-400">
                                    ({{ count($teacher['classes']) }})
                                </span>
                                @endif
                            </h2>

                            @if(!empty($teacher['classes']))
                            <div class="space-y-2">
                                @foreach($teacher['classes'] as $i => $class)
                                <div class="flex items-start gap-3 p-3 rounded-xl bg-white/40 border border-black/[0.04]">
                                    <div class="w-7 h-7 rounded-full bg-primary-50/80 text-primary-700
                                        flex items-center justify-center text-xs font-semibold flex-shrink-0 mt-0.5">
                                        {{ $i + 1 }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-sm text-slate-900 truncate">{{ $class['name'] }}</div>
                                        @if($class['school_year'])
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $class['school_year'] }}</div>
                                        @endif
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="rounded-xl bg-white/40 border border-black/[0.04] px-4 py-8 text-center">
                                <p class="text-sm font-medium text-slate-500">Chưa phụ trách lớp nào</p>
                            </div>
                            @endif
                        </section>

                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-2 px-1">
                                Tài khoản đăng nhập
                            </h2>
                            <div class="rounded-xl bg-white/40 border border-black/[0.04] px-4 py-3 space-y-2">
                                @if(!empty($teacher['has_account']))
                                <p class="text-sm font-semibold text-slate-700">Đã có tài khoản</p>
                                <p class="text-sm text-slate-500">
                                    Tên đăng nhập
                                    @if(!empty($teacher['login_is_phone']))
                                        (số điện thoại):
                                    @else
                                        (email):
                                    @endif
                                    <code class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">{{ $teacher['login_identifier'] ?: '—' }}</code>
                                </p>
                                <p class="text-sm text-slate-500">
                                    Mật khẩu mặc định (khi tạo / reset):
                                    @if(!empty($teacher['has_birthday']))
                                        chuỗi ngày sinh
                                        <code class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">{{ $teacher['default_password'] }}</code>
                                    @else
                                        <code class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">{{ $teacher['default_password'] }}</code>
                                        <span class="text-xs text-amber-600">(chưa có ngày sinh)</span>
                                    @endif
                                </p>
                                <p class="text-xs text-slate-400">Nhập đúng tên đăng nhập trên vào ô “Email hoặc SĐT” khi đăng nhập. Nếu GLV đã đổi mật khẩu thì mật khẩu hiện tại có thể khác.</p>
                                @if(!empty($teacher['perm_manage_parish_scores']) || !empty($teacher['perm_edit_parish_students']))
                                <div class="pt-2 border-t border-black/[0.04] space-y-1">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Quyền hỗ trợ quản trị</p>
                                    @if(!empty($teacher['perm_manage_parish_scores']))
                                    <p class="text-sm text-slate-700">• Quản lý điểm toàn giáo xứ</p>
                                    @endif
                                    @if(!empty($teacher['perm_edit_parish_students']))
                                    <p class="text-sm text-slate-700">• Sửa thông tin học sinh toàn giáo xứ</p>
                                    @endif
                                </div>
                                @endif
                                @else
                                <span class="text-sm text-slate-400 italic">Chưa tạo tài khoản</span>
                                @endif
                            </div>
                        </section>

                        <section>
                            <h2 class="text-xs font-semibold text-slate-500 tracking-wide uppercase mb-2 px-1">
                                Ghi chú
                            </h2>
                            <div class="text-sm text-slate-700 rounded-xl bg-white/40 border border-black/[0.04] p-3 min-h-[72px] leading-relaxed">
                                {{ !empty($teacher['note']) ? $teacher['note'] : 'Không có ghi chú' }}
                            </div>
                        </section>

                        <div class="px-1 space-y-1 text-xs text-slate-400">
                            <p>Tạo: {{ $teacher['created_at'] ?: '—' }}</p>
                            <p>Cập nhật: {{ $teacher['updated_at'] ?: '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </x-mac-panel>
    </div>
</div>
