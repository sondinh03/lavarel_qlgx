@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Quản lý giáo dân', 'url' => route('parishioners.index')],
    ['label' => 'Duyệt đăng ký', 'url' => route('parishioners.registrations.index')],
    ['label' => $registration->submitted_name],
]" />
@endsection

@php
    $statusBadge = match($registration->status) {
        'pending' => 'bg-amber-100 text-amber-800',
        'approved' => 'bg-emerald-100 text-emerald-800',
        'rejected' => 'bg-red-100 text-red-800',
        default => 'bg-slate-100 text-slate-700',
    };
    $members = $isFamilyRegister ? ($payload['members'] ?? []) : [];
    $familyInfo = $isFamilyRegister ? ($payload['family'] ?? []) : [];
    $memberName = function (?string $ref) use ($members) {
        foreach ($members as $m) {
            if (($m['ref'] ?? '') === $ref) {
                return trim(($m['last_name'] ?? '') . ' ' . ($m['first_name'] ?? ''));
            }
        }
        return $ref ?: '—';
    };
@endphp

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6">
    <div class="mx-auto max-w-4xl space-y-5">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-5 border-b border-slate-200 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-mono text-slate-500">{{ $registration->reference_code }}</p>
                    <p class="text-xs text-slate-400">Mã gia đình</p>
                    <h1 class="text-xl font-bold text-slate-900 mt-1">{{ $registration->submitted_name }}</h1>
                    <p class="text-sm text-slate-600 mt-1">
                        Gửi lúc {{ $registration->created_at->format('d/m/Y H:i') }}
                        @if($isFamilyRegister) · <span class="font-medium text-primary-600">Sổ gia đình</span> @endif
                    </p>
                </div>
                <span class="inline-flex px-3 py-1 rounded-xl text-sm font-semibold {{ $statusBadge }}">
                    {{ $registration->statusLabel() }}
                </span>
            </div>

            @if($isFamilyRegister)
            {{-- Sổ gia đình v2 --}}
            <div class="p-5 space-y-6">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-2">Hộ gia đình</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <div><span class="text-slate-500">Mã gia đình:</span> <span class="font-mono font-medium">{{ $familyInfo['code'] ?? $registration->reference_code }}</span></div>
                        <div><span class="text-slate-500">Tên hộ:</span> <span class="font-medium">{{ $familyInfo['name'] ?? '—' }}</span></div>
                        <div><span class="text-slate-500">Điện thoại liên hệ:</span> <span class="font-medium">{{ $payload['contact_phone'] ?? $registration->submitted_phone }}</span></div>
                        <div class="md:col-span-2"><span class="text-slate-500">Địa chỉ:</span> <span class="font-medium">{{ $familyInfo['address'] ?? '—' }}</span></div>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-2">Thành viên ({{ count($members) }})</h3>
                    <div class="space-y-2">
                        @foreach($members as $member)
                        <div class="rounded-xl border border-slate-200 p-3 text-sm">
                            <p class="font-semibold text-slate-900">
                                {{ trim(($member['last_name'] ?? '') . ' ' . ($member['first_name'] ?? '')) }}
                                @if(($member['ref'] ?? '') === ($payload['submitter_ref'] ?? ''))
                                <span class="text-xs text-primary-600 font-medium ml-1">(người đăng ký)</span>
                                @endif
                            </p>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ $familyRoles[$member['family_role'] ?? ''] ?? '—' }}
                                · {{ ($member['gender'] ?? '') === 'female' ? 'Nữ' : 'Nam' }}
                                @if(!empty($member['birthday'])) · {{ $member['birthday'] }} @endif
                                @if(!empty($member['birth_place'])) · {{ $member['birth_place'] }} @endif
                            </p>
                            @if(!empty($member['father_ref']) || !empty($member['mother_ref']) || !empty($member['father_name']) || !empty($member['mother_name']))
                            <p class="text-xs text-slate-500 mt-1">
                                Cha: {{ !empty($member['father_ref']) ? $memberName($member['father_ref']) : ($member['father_name'] ?? '—') }}
                                · Mẹ: {{ !empty($member['mother_ref']) ? $memberName($member['mother_ref']) : ($member['mother_name'] ?? '—') }}
                            </p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                @if(!empty($marriages))
                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-2">Hôn phối ({{ count($marriages) }})</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach($marriages as $row)
                        <li class="rounded-xl border border-slate-200 p-3">
                            <span class="font-semibold">{{ $memberName($row['husband_ref'] ?? null) }}</span>
                            <span class="text-slate-400">&</span>
                            <span class="font-semibold">{{ $memberName($row['wife_ref'] ?? null) }}</span>
                            <p class="text-xs text-slate-500 mt-1">
                                @if(!empty($row['married_date'])) Ngày HP: {{ $row['married_date'] }} @endif
                                @if(!empty($row['certificate_number'])) · Số: {{ $row['certificate_number'] }} @endif
                                @if(!empty($row['parish_name'])) · {{ $row['parish_name'] }} @endif
                                @if(!empty($row['priest_witness'])) · LM: {{ $row['priest_witness'] }} @endif
                            </p>
                            @if(!empty($row['witness_1']) || !empty($row['witness_2']))
                            <p class="text-xs text-slate-500">Nhân chứng: {{ trim(($row['witness_1'] ?? '') . ', ' . ($row['witness_2'] ?? ''), ', ') }}</p>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(!empty($sacraments))
                <div>
                    <h3 class="text-sm font-bold text-slate-800 mb-2">Bí tích ({{ count($sacraments) }})</h3>
                    <ul class="space-y-1 text-sm text-slate-600">
                        @foreach($sacraments as $row)
                        <li>• {{ $memberName($row['member_ref'] ?? null) }} — {{ $typeOptions[$row['type'] ?? ''] ?? ($row['type'] ?? '') }}
                            @if(!empty($row['received_date'])) ({{ $row['received_date'] }}) @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            @else
            {{-- Đăng ký cá nhân cũ --}}
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><span class="text-slate-500">Điện thoại:</span> <span class="font-medium">{{ $payload['phone'] ?? $registration->submitted_phone }}</span></div>
                <div><span class="text-slate-500">Email:</span> <span class="font-medium">{{ $payload['email'] ?? '—' }}</span></div>
                <div><span class="text-slate-500">Giới tính:</span> <span class="font-medium">{{ ($payload['gender'] ?? '') === 'female' ? 'Nữ' : 'Nam' }}</span></div>
                <div><span class="text-slate-500">Ngày sinh:</span> <span class="font-medium">{{ $payload['birthday'] ?? '—' }}</span></div>
                <div><span class="text-slate-500">Vai trò GĐ:</span> <span class="font-medium">{{ $familyRoles[$payload['family_role'] ?? ''] ?? '—' }}</span></div>
                <div><span class="text-slate-500">Cha:</span> <span class="font-medium">{{ $payload['father_name'] ?? '—' }}</span></div>
                <div><span class="text-slate-500">Mẹ:</span> <span class="font-medium">{{ $payload['mother_name'] ?? '—' }}</span></div>
            </div>
            @if(!empty($sacraments))
            <div class="px-5 pb-5 border-t border-slate-100 pt-4">
                <h3 class="text-sm font-semibold text-slate-800 mb-2">Bí tích</h3>
                <ul class="space-y-1 text-sm text-slate-600">
                    @foreach($sacraments as $row)
                    <li>• {{ $typeOptions[$row['type'] ?? ''] ?? ($row['type'] ?? '') }}
                        @if(!empty($row['received_date'])) — {{ $row['received_date'] }} @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
            @endif

            @if($registration->isApproved())
            <div class="px-5 pb-5 flex flex-wrap gap-4">
                @if($registration->family_id)
                <a href="{{ route('families.show', $registration->family_id) }}"
                    class="text-primary-600 font-semibold text-sm hover:text-primary-700">
                    Xem hộ gia đình đã tạo →
                </a>
                @endif
                @if($registration->parishioner_id)
                <a href="{{ route('parishioners.show', $registration->parishioner_id) }}"
                    class="text-primary-600 font-semibold text-sm hover:text-primary-700">
                    Xem hồ sơ người đăng ký →
                </a>
                @endif
            </div>
            @endif

            @if($registration->status === 'rejected' && $registration->rejection_reason)
            <div class="mx-5 mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-sm text-red-800">
                <strong>Lý do từ chối:</strong> {{ $registration->rejection_reason }}
            </div>
            @endif
        </div>

        @if($registration->isPending())
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-200 p-5 space-y-4">
            <div class="flex items-start gap-3 p-4 rounded-xl bg-emerald-50 border border-emerald-100">
                <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-emerald-900">Yêu cầu đang chờ duyệt</p>
                    <p class="text-xs text-emerald-700 mt-1">Nhấn <strong>Duyệt</strong> để thêm vào hệ thống, hoặc <strong>Từ chối</strong> nếu thông tin chưa đúng.</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Ghi chú nội bộ (tùy chọn)</label>
                <textarea wire:model.defer="adminNote" rows="2"
                    class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" wire:click="openApproveModal"
                    class="flex-1 px-5 py-3 rounded-xl bg-emerald-500 text-white text-sm font-bold hover:opacity-90 shadow-sm transition">
                    ✓ Duyệt và thêm vào hệ thống
                </button>
                <button type="button" wire:click="openRejectModal"
                    class="flex-1 sm:flex-none px-5 py-3 rounded-xl border-2 border-red-300 text-red-700 text-sm font-semibold hover:bg-red-50">
                    Từ chối
                </button>
                <a href="{{ route('parishioners.registrations.index') }}"
                    class="px-5 py-3 rounded-xl border border-slate-300 text-sm font-medium text-slate-700 text-center hover:bg-slate-50">
                    Quay lại
                </a>
            </div>
        </div>
        @endif
    </div>

    @if($showApproveModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-5 space-y-4">
            <h3 class="text-lg font-bold text-slate-900">Xác nhận duyệt</h3>
            <p class="text-sm text-slate-600">
                {{ $isFamilyRegister
                    ? 'Hệ thống sẽ tạo hộ gia đình, thành viên, hôn phối và bí tích từ dữ liệu đã gửi.'
                    : 'Hệ thống sẽ tạo hồ sơ giáo dân từ dữ liệu đã gửi.' }}
            </p>
            <div class="flex gap-2 justify-end">
                <button type="button" wire:click="closeApproveModal" class="px-4 py-2 rounded-xl border text-sm">Hủy</button>
                <button type="button" wire:click="approve" wire:loading.attr="disabled"
                    class="px-4 py-2 rounded-xl bg-emerald-500 text-white text-sm font-semibold hover:opacity-90 disabled:opacity-50 transition">
                    <span wire:loading.remove wire:target="approve">Xác nhận duyệt</span>
                    <span wire:loading wire:target="approve">Đang xử lý...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    @if($showRejectModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-5 space-y-4">
            <h3 class="text-lg font-bold text-slate-900">Từ chối yêu cầu</h3>
            <textarea wire:model.defer="rejectionReason" rows="4" placeholder="Nhập lý do từ chối..."
                class="w-full px-3 py-2 rounded-xl border border-slate-300 text-sm"></textarea>
            @error('rejectionReason') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
            <div class="flex gap-2 justify-end">
                <button type="button" wire:click="closeRejectModal" class="px-4 py-2 rounded-xl border text-sm">Hủy</button>
                <button type="button" wire:click="reject" class="px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-semibold">
                    Xác nhận từ chối
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
