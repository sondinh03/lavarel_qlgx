@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ',       'url' => route('dashboard')],
    ['label' => 'Sinh hoạt',      'url' => '#'],
    ['label' => $group->name,     'url' => route('groups.members', $group->id)],
    ['label' => 'Buổi sinh hoạt', 'url' => route('groups.sessions', $group->id)],
    ['label' => $session->date->format('d/m/Y') . ' · ' . $session->shift_label],
]" separator="arrow" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6"
    x-data="{
        records: {},
        draft: {},
        isSaving: false,

        getStatus(memberId) {
            const key = memberId + '_' + {{ $sessionId }};
            if (this.draft[key] !== undefined) return this.draft[key].status;
            return this.records[key]?.status ?? null;
        },

        getNote(memberId) {
            const key = memberId + '_' + {{ $sessionId }};
            if (this.draft[key] !== undefined) return this.draft[key].note || null;
            return this.records[key]?.note || null;
        },

        hasDraft() { return Object.keys(this.draft).length > 0; },
        draftCount() { return Object.keys(this.draft).length; },

        toggle(memberId, status) {
            const key     = memberId + '_' + {{ $sessionId }};
            const current = this.getStatus(memberId);
            if (current === status) {
                delete this.draft[key];
            } else {
                this.draft[key] = { status: status, note: this.getNote(memberId) || '' };
            }
        },

        markAll() {
            @foreach($members as $member)
            this.draft[{{ $member->id }} + '_' + {{ $sessionId }}] = { status: 1, note: '' };
            @endforeach
        },

        save() {
            if (!this.hasDraft() || this.isSaving) return;
            this.isSaving = true;
            @this.call('saveFromClient', this.draft);
        },

        discard() { this.draft = {}; },

        noteModal: {
            open: false,
            memberId: null,
            memberName: '',
            note: '',
        },

        openNote(memberId, memberName) {
            this.noteModal = {
                open: true,
                memberId,
                memberName,
                note: this.getNote(memberId) || '',
            };
        },

        saveNote() {
            const key = this.noteModal.memberId + '_' + {{ $sessionId }};
            this.draft[key] = { status: 2, note: this.noteModal.note };
            this.noteModal.open = false;
        },
    }"
    x-init="
        records = @js($attendanceRecords);
    "
    x-on:attendance-saved.window="
        isSaving = false;
        draft = {};
        if ($event.detail.records) {
            Object.keys(records).forEach(k => delete records[k]);
            Object.assign(records, $event.detail.records);
        }
    "
    x-on:attendance-save-error.window="isSaving = false;">

    <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

    <div id="main-content" class="mx-auto max-w-4xl space-y-6">

        {{-- Toast --}}
        <div role="status" aria-live="polite">
            @if(session()->has('message'))
                <x-toast-notification type="success" :duration="3500">{{ session('message') }}</x-toast-notification>
            @endif
            @if(session()->has('error'))
                <x-toast-notification type="error" :duration="4000">{{ session('error') }}</x-toast-notification>
            @endif
        </div>

        {{-- Header Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="p-6 border-b border-slate-200">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-bold text-slate-900">
                            Điểm danh · {{ $group->name }}
                        </h1>
                        <div class="flex items-center gap-3 mt-2 flex-wrap text-sm text-slate-600">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full
                                         text-xs font-semibold
                                {{ $session->shift == 1 ? 'bg-amber-100 text-amber-700' :
                                  ($session->shift == 2 ? 'bg-blue-100 text-blue-700' :
                                                          'bg-indigo-100 text-indigo-700') }}">
                                {{ $session->shift_label }}
                            </span>
                            <span class="font-semibold text-slate-800">
                                {{ $session->date->isoFormat('dddd, DD/MM/YYYY') }}
                            </span>
                            <span class="text-slate-400">·</span>
                            <span>{{ $session->type_label }}</span>
                            @if($session->title)
                                <span class="text-slate-400">·</span>
                                <span class="italic">{{ $session->title }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="flex items-center gap-4 flex-shrink-0">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $stats['present'] }}</div>
                            <div class="text-xs text-slate-500">Có mặt</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-500">{{ $stats['excused'] }}</div>
                            <div class="text-xs text-slate-500">Có phép</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-500">{{ $stats['absent'] }}</div>
                            <div class="text-xs text-slate-500">Vắng</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-slate-600">{{ $memberCount }}</div>
                            <div class="text-xs text-slate-500">Tổng</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action bar --}}
            <div class="px-6 py-3 bg-slate-50/70 flex items-center justify-between gap-3">
                <button type="button" x-on:click="markAll()"
                    class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-semibold
                           text-green-700 bg-green-100 hover:bg-green-200 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                    </svg>
                    Tất cả có mặt
                </button>

                <div class="flex items-center gap-3">
                    {{-- Discard --}}
                    <button type="button"
                        x-show="hasDraft()"
                        x-on:click="discard()"
                        class="px-4 py-2 border border-red-300 text-red-700 rounded-xl
                               hover:bg-red-50 transition-colors text-sm font-medium">
                        Hủy thay đổi
                    </button>

                    {{-- Save --}}
                    <button type="button"
                        x-on:click="save()"
                        :disabled="!hasDraft() || isSaving"
                        :class="!hasDraft() || isSaving
                            ? 'bg-slate-200 text-slate-400 cursor-not-allowed'
                            : 'bg-primary-600 hover:bg-primary-700 text-white cursor-pointer'"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-colors
                               flex items-center gap-2">
                        <svg x-show="isSaving" x-cloak
                            class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <svg x-show="!isSaving" class="w-4 h-4" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        <span x-show="!isSaving">
                            Lưu
                            <span x-show="hasDraft()"
                                x-text="'(' + draftCount() + ')'"
                                class="ml-1"></span>
                        </span>
                        <span x-show="isSaving" x-cloak>Đang lưu...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Attendance Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @if($members->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full border-separate border-spacing-0">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase w-10">#</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">
                                    {{ $group->member_type === 'teacher' ? 'Giáo lý viên' : 'Học sinh' }}
                                    <span class="ml-1 font-normal text-slate-400">({{ $memberCount }})</span>
                                </th>
                                @if($group->member_type === 'teacher')
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">SĐT</th>
                                @else
                                    <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Giáo họ</th>
                                @endif
                                <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase">Điểm danh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($members as $index => $member)
                            @php $person = $member->memberable; @endphp
                            <tr wire:key="member-row-{{ $member->id }}"
                                :class="{
                                    'bg-green-50/40': getStatus({{ $member->id }}) == 1,
                                    'bg-yellow-50/40': getStatus({{ $member->id }}) == 2,
                                    'bg-red-50/30': getStatus({{ $member->id }}) == 3,
                                }"
                                class="transition-colors">

                                {{-- STT --}}
                                <td class="px-4 py-3 text-sm text-slate-400">
                                    {{ $index + 1 }}
                                </td>

                                {{-- Tên --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center
                                                    text-xs font-bold bg-primary-50 text-primary-700">
                                            {{ mb_substr($person?->first_name ?? '?', 0, 1) }}
                                        </div>
                                        <div>
                                            @if($person?->saint)
                                                <div class="text-xs text-slate-400">{{ $person->saint->name }}</div>
                                            @endif
                                            <div class="text-sm font-semibold text-slate-900">
                                                {{ $person?->full_name ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- SĐT / Giáo họ --}}
                                <td class="px-4 py-3 text-sm text-slate-600">
                                    @if($group->member_type === 'teacher')
                                        {{ $person?->phone_number ?? '—' }}
                                    @else
                                        {{ $person?->parishGroup?->name ?? '—' }}
                                    @endif
                                </td>

                                {{-- Điểm danh buttons --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-2">

                                        {{-- Có mặt --}}
                                        <button type="button"
                                            x-on:click="toggle({{ $member->id }}, 1)"
                                            :class="getStatus({{ $member->id }}) == 1
                                                ? 'bg-green-500 text-white shadow-md ring-2 ring-green-300 scale-105'
                                                : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100'"
                                            class="w-10 h-10 rounded-xl flex items-center justify-center
                                                   transition-all active:scale-95"
                                            aria-label="Có mặt">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>

                                        {{-- Vắng có phép --}}
                                        <button type="button"
                                            x-on:click="openNote({{ $member->id }}, '{{ addslashes($person?->full_name ?? '') }}')"
                                            :class="getStatus({{ $member->id }}) == 2
                                                ? 'bg-yellow-400 text-slate-900 shadow-md ring-2 ring-yellow-300 scale-105'
                                                : 'bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-100'"
                                            class="w-10 h-10 rounded-xl flex items-center justify-center
                                                   font-bold text-base transition-all active:scale-95 relative"
                                            aria-label="Vắng có phép">
                                            P
                                            <span x-show="getNote({{ $member->id }})"
                                                class="absolute -top-1 -right-1 w-2 h-2
                                                       bg-primary-500 rounded-full ring-2 ring-white">
                                            </span>
                                        </button>

                                        {{-- Vắng không phép --}}
                                        <button type="button"
                                            x-on:click="toggle({{ $member->id }}, 3)"
                                            :class="getStatus({{ $member->id }}) == 3
                                                ? 'bg-red-500 text-white shadow-md ring-2 ring-red-300 scale-105'
                                                : 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100'"
                                            class="w-10 h-10 rounded-xl flex items-center justify-center
                                                   transition-all active:scale-95"
                                            aria-label="Vắng không phép">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>

                                        {{-- Đi trễ --}}
                                        <button type="button"
                                            x-on:click="toggle({{ $member->id }}, 4)"
                                            :class="getStatus({{ $member->id }}) == 4
                                                ? 'bg-orange-400 text-white shadow-md ring-2 ring-orange-300 scale-105'
                                                : 'bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-100'"
                                            class="w-10 h-10 rounded-xl flex items-center justify-center
                                                   font-bold text-xs transition-all active:scale-95"
                                            aria-label="Đi trễ">
                                            T
                                        </button>

                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Legend --}}
                <div class="px-6 py-3 border-t border-slate-100 bg-slate-50">
                    <div class="flex items-center gap-4 flex-wrap text-xs text-slate-500">
                        <span class="flex items-center gap-1.5">
                            <span class="w-6 h-6 rounded-lg bg-green-500 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                </svg>
                            </span>
                            Có mặt
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-6 h-6 rounded-lg bg-yellow-400 flex items-center justify-center
                                         font-bold text-slate-900 text-xs">P</span>
                            Vắng có phép
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-6 h-6 rounded-lg bg-red-500 flex items-center justify-center">
                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </span>
                            Vắng không phép
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-6 h-6 rounded-lg bg-orange-400 flex items-center justify-center
                                         font-bold text-white text-xs">T</span>
                            Đi trễ
        </span>
                    </div>
                </div>

            @else
                <x-empty-state
                    icon="users"
                    :colspan="4"
                    title="Nhóm chưa có thành viên"
                    description="Thêm thành viên vào nhóm trước khi điểm danh" />
            @endif
        </div>

        {{-- Mobile sticky save bar --}}
        <div class="lg:hidden fixed left-0 right-0 z-20 bg-white border-t border-slate-200 shadow-lg px-4"
            style="bottom: calc(env(safe-area-inset-bottom) + 60px); padding-top:12px; padding-bottom:12px;">
            <div class="flex items-center gap-3 max-w-7xl mx-auto">
                <button type="button"
                    x-on:click="discard()"
                    :disabled="!hasDraft() || isSaving"
                    :class="!hasDraft() || isSaving
                        ? 'border-slate-200 text-slate-400 cursor-not-allowed bg-slate-100'
                        : 'border-red-200 text-red-500 hover:bg-red-50'"
                    class="flex-shrink-0 w-14 h-14 rounded-xl border flex items-center justify-center transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <button type="button"
                    x-on:click="save()"
                    :disabled="!hasDraft() || isSaving"
                    :class="!hasDraft() || isSaving
                        ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                        : 'bg-primary-600 text-white active:scale-95'"
                    class="flex-1 h-14 rounded-xl font-semibold text-sm transition-all
                           flex items-center justify-center gap-2">
                    <svg x-show="isSaving" x-cloak class="animate-spin w-4 h-4"
                        fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-show="!isSaving">
                        Lưu điểm danh
                        <span x-show="hasDraft()" x-text="'(' + draftCount() + ')'" class="ml-1"></span>
                    </span>
                    <span x-show="isSaving" x-cloak>Đang lưu...</span>
                </button>
            </div>
        </div>

        <div class="lg:hidden h-24"></div>

    </div>{{-- /max-w --}}

    {{-- ===================== NOTE MODAL ===================== --}}
    <div x-show="noteModal.open" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 bg-black/40 z-50 flex items-end lg:items-center justify-center lg:p-4"
        x-on:click="noteModal.open = false">

        <div x-show="noteModal.open" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full lg:translate-y-0 lg:scale-95 lg:opacity-0"
            x-transition:enter-end="translate-y-0 lg:scale-100 lg:opacity-100"
            class="w-full lg:w-[480px] lg:rounded-2xl rounded-t-2xl bg-white"
            x-on:click.stop>

            {{-- Handle mobile --}}
            <div class="flex justify-center pt-3 pb-2 lg:hidden">
                <div class="w-10 h-1 bg-slate-300 rounded-full"></div>
            </div>

            {{-- Header desktop --}}
            <div class="hidden lg:flex items-center justify-between px-5 py-4 border-b border-slate-200">
                <div>
                    <h3 class="font-semibold text-slate-900">Vắng có phép</h3>
                    <p class="text-sm text-slate-500 mt-0.5" x-text="noteModal.memberName"></p>
                </div>
                <button type="button" x-on:click="noteModal.open = false"
                    class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Name mobile --}}
            <div class="px-4 pb-3 lg:hidden">
                <span class="text-xs text-slate-400">Vắng có phép · </span>
                <span class="text-sm font-semibold text-slate-700" x-text="noteModal.memberName"></span>
            </div>

            {{-- Quick reasons --}}
            <div class="px-4 lg:px-5 lg:py-4 grid grid-cols-2 gap-2">
                @foreach(['Bệnh', 'Về quê', 'Gia đình có việc', 'Lý do khác'] as $reason)
                <button type="button"
                    x-on:click="noteModal.note = '{{ $reason }}'; saveNote()"
                    class="py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm
                           text-slate-700 hover:bg-yellow-50 hover:border-yellow-300
                           active:scale-95 transition-all font-medium">
                    {{ $reason }}
                </button>
                @endforeach
            </div>

            {{-- Custom input --}}
            <div class="px-4 lg:px-5 pt-3 flex gap-2"
                style="padding-bottom: calc(1rem + env(safe-area-inset-bottom))">
                <input type="text"
                    x-model="noteModal.note"
                    x-on:keydown.enter="if(noteModal.note.trim()) saveNote()"
                    placeholder="Lý do khác..."
                    class="flex-1 px-3 py-2.5 rounded-xl border border-slate-300 text-sm
                           focus:outline-none focus:ring-2 focus:ring-yellow-500" />
                <button type="button"
                    x-on:click="if(noteModal.note.trim()) saveNote()"
                    :class="noteModal.note.trim()
                        ? 'bg-yellow-500 hover:bg-yellow-600 text-white active:scale-95'
                        : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold transition-all">
                    OK
                </button>
            </div>
        </div>
    </div>

</div>

@push('page-title')
<span class="text-slate-800 font-semibold text-sm">
    Điểm danh · {{ $group->name }} · {{ $session->date->format('d/m/Y') }}
</span>
@endpush

@push('scripts')
<script>
    document.addEventListener('livewire:load', function () {
        // Ctrl+S / Cmd+S → save
        window.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                const el = document.querySelector('[x-data]');
                if (el) Alpine.evaluate(el, 'save()');
            }
        });
    });
</script>
@endpush