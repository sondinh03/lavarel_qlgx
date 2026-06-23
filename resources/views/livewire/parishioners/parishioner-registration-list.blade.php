@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')],
    ['label' => 'Quản lý giáo dân', 'url' => route('parishioners.index')],
    ['label' => 'Duyệt đăng ký giáo dân'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6">
    <div class="mx-auto max-w-6xl space-y-5">

        <x-page-header
            icon-type="parishioners"
            title="Duyệt đăng ký giáo dân"
            description="Yêu cầu tự khai từ giáo dân qua điện thoại">
            <x-slot name="actions">
                @if($pendingCount > 0)
                <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-amber-100 text-amber-800">
                    {{ $pendingCount }} chờ duyệt
                </span>
                @endif
                <x-button as="a" href="{{ route('parishioners.register.public') }}" variant="outline" target="_blank">
                    Mở form công khai
                </x-button>
            </x-slot>
        </x-page-header>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 space-y-4">
            <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <div class="flex flex-wrap gap-2">
                    @foreach($statuses as $value => $label)
                    <button type="button" wire:click="$set('statusFilter', '{{ $value }}')"
                        class="px-3 py-1.5 rounded-xl text-sm font-medium transition
                            {{ $statusFilter === $value ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
                <input wire:model.debounce.400ms="search" type="search" placeholder="Tìm tên, SĐT, mã..."
                    class="w-full sm:w-64 px-3 py-2 rounded-xl border border-slate-300 text-sm" />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500">
                            <th class="py-2 pr-3 font-medium">Mã</th>
                            <th class="py-2 pr-3 font-medium">Họ tên</th>
                            <th class="py-2 pr-3 font-medium">Điện thoại</th>
                            <th class="py-2 pr-3 font-medium">Vai trò GĐ</th>
                            <th class="py-2 pr-3 font-medium">Ngày gửi</th>
                            <th class="py-2 pr-3 font-medium">Trạng thái</th>
                            <th class="py-2 font-medium">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($registrations as $item)
                        <tr class="hover:bg-slate-50">
                            <td class="py-3 pr-3 font-mono text-xs">{{ $item->reference_code }}</td>
                            <td class="py-3 pr-3">
                                <a href="{{ route('parishioners.registrations.show', $item) }}"
                                    class="font-semibold text-primary-600 hover:text-primary-700">
                                    {{ $item->submitted_name }}
                                </a>
                            </td>
                            <td class="py-3 pr-3">{{ $item->submitted_phone }}</td>
                            <td class="py-3 pr-3">{{ $item->familyRoleLabel() ?? '—' }}</td>
                            <td class="py-3 pr-3 text-slate-500">{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3">
                                @php
                                    $badge = match($item->status) {
                                        'pending' => 'bg-amber-100 text-amber-800',
                                        'approved' => 'bg-emerald-100 text-emerald-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex px-2 py-0.5 rounded-lg text-xs font-semibold {{ $badge }}">
                                    {{ $item->statusLabel() }}
                                </span>
                            </td>
                            <td class="py-3 overflow-visible">
                                <div class="flex items-center justify-center gap-1">
                                    <x-tooltip content="Xem chi tiết">
                                        <a href="{{ route('parishioners.registrations.show', $item) }}"
                                            class="p-2 hover:bg-slate-100 text-slate-600 rounded-lg transition-all">
                                            <x-icon name="eye" />
                                        </a>
                                    </x-tooltip>

                                    @if($item->isPending())
                                    <x-tooltip content="Duyệt">
                                        <button type="button"
                                            x-on:click="$dispatch('open-confirm', {
                                                message: 'Duyệt yêu cầu này và thêm vào hệ thống?',
                                                wireMethod: 'approve({{ $item->id }})'
                                            })"
                                            wire:loading.attr="disabled"
                                            wire:target="approve"
                                            class="p-2 hover:bg-green-50 text-green-600 rounded-lg transition-all disabled:opacity-50">
                                            <x-icon name="check" />
                                        </button>
                                    </x-tooltip>

                                    <x-tooltip content="Từ chối">
                                        <button type="button"
                                            wire:click="openRejectModal({{ $item->id }})"
                                            class="p-2 hover:bg-red-50 text-red-600 rounded-lg transition-all">
                                            <x-icon name="cancel" />
                                        </button>
                                    </x-tooltip>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500">Không có yêu cầu nào.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>{{ $registrations->links() }}</div>
        </div>
    </div>

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
