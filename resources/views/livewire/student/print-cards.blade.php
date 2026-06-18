@section('topbar')
<x-breadcrumb :items="[
    ['label' => 'Trang chủ', 'url' => route('parish-admin.dashboard')],
    ['label' => 'Học sinh', 'url' => route('students.index')],
    ['label' => 'In thẻ học sinh'],
]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6 print:bg-white print:p-0"
    style="min-height: calc(100vh - 56px - var(--bottom-offset));"
    x-data="{
        downloading: false,
        async downloadPdf() {
            this.downloading = true;
            await this.$nextTick();

            const area = document.getElementById('print-area');
            if (!area) {
                this.downloading = false;
                return;
            }

            area.style.display = 'block';
            area.style.visibility = 'visible';

            await Promise.all(
                [...area.querySelectorAll('img')]
                    .filter(img => !img.complete)
                    .map(img => new Promise(resolve => {
                        img.onload = img.onerror = resolve;
                    }))
            );

            try {
                await html2pdf().set({
                    margin: [8, 8, 8, 8],
                    filename: '{{ \Str::slug(($cardType === 'annual' ? 'the-nam-hoc' : 'the-vinh-vien') . '-' . ($lop->name ?? 'hoc-sinh') . '-' . ($parishName ?: 'giao-xu')) }}.pdf',
                    image: { type: 'jpeg', quality: 0.95 },
                    html2canvas: {
                        scale: 2,
                        useCORS: true,
                        allowTaint: false,
                        logging: false,
                        scrollY: 0,
                        scrollX: 0,
                        windowWidth: document.documentElement.scrollWidth,
                        windowHeight: document.documentElement.scrollHeight,
                    },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                    pagebreak: { mode: ['css', 'legacy'] },
                }).from(area).save();
            } finally {
                area.style.display = 'none';
                area.style.visibility = '';
                this.downloading = false;
            }
        }
    }">

    <a href="#print-cards-main" class="sr-only focus:not-sr-only print:hidden">Bỏ qua tới nội dung</a>

    {{-- ══ UI (ẩn khi in) ══ --}}
    <div id="print-cards-main" class="print:hidden mx-auto max-w-5xl space-y-6">

        @if(session()->has('message') || session()->has('warning') || session()->has('error'))
        <div role="status" aria-live="polite">
            @if(session()->has('message'))
            <x-toast-notification type="success" :duration="3500">{{ session('message') }}</x-toast-notification>
            @endif
            @if(session()->has('warning'))
            <x-toast-notification type="warning" :duration="4000">{{ session('warning') }}</x-toast-notification>
            @endif
            @if(session()->has('error'))
            <x-toast-notification type="error" :duration="4000">{{ session('error') }}</x-toast-notification>
            @endif
        </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            @php
            $headerDesc = collect([
                $lop ? 'Lớp ' . $lop->name : null,
                $lop?->schoolYear?->name,
            ])->filter()->implode(' · ');
            if ($students->count() > 0) {
                $headerDesc .= ($headerDesc ? ' · ' : '') . $students->count() . ' thẻ · ' . ceil($students->count() / 8) . ' trang A4';
            } elseif ($headerDesc === '') {
                $headerDesc = 'Chọn học sinh hoặc lớp để in thẻ CR80';
            }
            @endphp

            <x-page-header
                class="rounded-t-2xl"
                icon-type="students"
                title="In thẻ học sinh"
                :description="$headerDesc"
                :count="$students->count() ?: null">
                <x-slot name="actions">
                    <x-button as="a" href="javascript:history.back()" variant="subtle">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Quay lại
                    </x-button>

                    @if($students->count() > 0)
                    <x-button type="button" variant="success" @click="downloadPdf()" x-bind:disabled="downloading">
                        <template x-if="!downloading">
                            <x-icon name="download" />
                        </template>
                        <template x-if="downloading">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                        </template>
                        <span x-text="downloading ? 'Đang tạo PDF...' : 'Tải PDF'"></span>
                    </x-button>

                    <x-button wire:click="printCards" variant="primary">
                        <x-icon name="printer" />
                        In {{ $students->count() }} thẻ
                    </x-button>
                    @endif
                </x-slot>
            </x-page-header>

            @if($students->count() > 0)
            <div class="px-4 lg:px-6 py-4 border-t border-slate-200 bg-slate-50/70">
                <p class="text-sm font-semibold text-slate-700 mb-2">Loại thẻ</p>
                <div class="inline-flex w-full sm:w-auto rounded-xl bg-slate-200 p-1 text-sm font-medium">
                    <button type="button" wire:click="$set('cardType', 'permanent')"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg transition-all
                            {{ $cardType === 'permanent'
                                ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        Thẻ vĩnh viễn
                        <span class="hidden sm:inline text-xs font-normal text-slate-400">(không lớp/năm)</span>
                    </button>
                    <button type="button" wire:click="$set('cardType', 'annual')"
                        class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg transition-all
                            {{ $cardType === 'annual'
                                ? 'bg-white shadow-sm text-primary-600 font-semibold'
                                : 'text-slate-600 hover:text-primary-600 hover:bg-white/50' }}">
                        Thẻ theo năm học
                        <span class="hidden sm:inline text-xs font-normal text-slate-400">(có lớp + năm)</span>
                    </button>
                </div>
                @if($parishName)
                <p class="mt-2 text-xs text-slate-500">
                    Giáo xứ trên thẻ: <span class="font-semibold text-slate-700">{{ $parishName }}</span>
                </p>
                @endif
            </div>

            <div class="p-4 lg:p-6 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">
                        Xem trước · 2 thẻ / hàng · 85.6×54mm (CR80)
                    </p>
                    <span class="text-xs text-slate-500">
                        {{ $students->count() }} thẻ
                    </span>
                </div>

                <div class="preview-grid" wire:key="preview-{{ $cardType }}">
                    @foreach($students as $student)
                    @include('livewire.student.student-card', [
                        'student' => $student,
                        'lop' => $lop,
                        'cardType' => $cardType,
                        'parishName' => $parishName,
                    ])
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        @if($students->count() === 0)
        <x-stats.page-empty
            title="Không có học sinh nào để in"
            description="Truyền ?ids=1,2,3 hoặc ?classId=X trên URL">
            <x-slot name="icon">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </x-slot>
            <p class="mt-3 text-xs text-slate-400 font-mono">
                ?ids=1,2,3 · ?classId=5
            </p>
        </x-stats.page-empty>
        @endif
    </div>

    {{-- ══ Vùng in / xuất PDF ══ --}}
    @if($students->count() > 0)
    <div id="print-area"
        wire:key="print-{{ $cardType }}"
        style="display:none; width: 794px; padding: 38px; box-sizing: border-box;">
        @foreach($students->chunk(8) as $chunk)
        <div class="print-page">
            <div class="print-grid">
                @foreach($chunk as $student)
                @include('livewire.student.student-card', [
                    'student' => $student,
                    'lop' => $lop,
                    'cardType' => $cardType,
                    'parishName' => $parishName,
                    'forPrint' => true,
                ])
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    window.addEventListener('trigger-print', function() {
        const area = document.getElementById('print-area');
        if (!area) return;
        area.style.display = 'block';
        window.print();
        setTimeout(() => { area.style.display = 'none'; }, 1000);
    });
</script>
@endpush

@push('styles')
<style>
    .preview-grid {
        display: grid;
        grid-template-columns: repeat(2, max-content);
        gap: 16px;
        justify-content: center;
    }

    @media (max-width: 900px) {
        .preview-grid {
            grid-template-columns: repeat(1, max-content);
        }

        .preview-grid .student-card {
            transform: scale(0.85);
            transform-origin: top left;
            margin-bottom: -8mm;
        }
    }

    .print-grid {
        display: grid;
        grid-template-columns: 323px 323px;
        gap: 23px;
        width: 669px;
        max-width: 669px;
    }

    .print-page {
        page-break-after: always;
        break-after: page;
    }

    .print-page:last-child {
        page-break-after: avoid;
        break-after: avoid;
    }

    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
        }

        .student-card {
            break-inside: avoid !important;
            page-break-inside: avoid !important;
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        .print-page {
            page-break-after: always;
            break-after: page;
        }

        .print-page:last-child {
            page-break-after: avoid;
            break-after: avoid;
        }
    }
</style>
@endpush
