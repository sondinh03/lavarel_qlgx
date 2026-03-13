<div class="min-h-screen bg-slate-100 print:bg-white print:p-0"
    x-data="{
        downloading: false,
        async downloadPdf() {
        this.downloading = true;
        await this.$nextTick();

        const area = document.getElementById('print-area');

        // Hiện vùng in tạm thời để html2pdf chụp
        area.style.display = 'block';
        area.style.visibility = 'visible';

        // Chờ ảnh load xong (quan trọng nếu dùng QR từ API)
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
                filename: '{{ \Str::slug(($lop->name ?? "the-hoc-sinh") . "-" . ($lop->schoolYear->name ?? "")) }}.pdf',
                image: { type: 'jpeg', quality: 0.95 },
                html2canvas: { scale: 2, useCORS: true, allowTaint: false, logging: false, 
                scrollY: 0, scrollX: 0, windowWidth: document.documentElement.scrollWidth, windowHeight: document.documentElement.scrollHeight, },
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

    {{-- ══ UI (ẩn khi print) ══ --}}
    <div class="print:hidden mx-auto max-w-5xl px-4 py-6 space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('dashboard')],
            ['label' => 'Học sinh', 'url' => route('students.index')],
            ['label' => 'In thẻ học sinh'],
        ]" separator="arrow" />

        {{-- Toast --}}
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

        {{-- Header card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
            <div class="px-6 py-5 flex items-start justify-between gap-4 flex-wrap">

                <div>
                    <h1 class="text-xl font-bold text-slate-900">In thẻ học sinh</h1>
                    <p class="text-sm text-slate-500 mt-1">
                        @if($lop)
                        Lớp <span class="font-semibold text-slate-700">{{ $lop->name }}</span>
                        @if($lop->schoolYear) · <span class="font-semibold text-slate-700">{{ $lop->schoolYear->name }}</span> @endif
                        ·
                        @endif
                        <span class="font-semibold text-blue-600">{{ $students->count() }} thẻ</span>
                        @if($students->count() > 0)
                        · <span class="text-slate-400">{{ ceil($students->count() / 8) }} trang A4</span>
                        @endif
                    </p>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Quay lại --}}
                    <a href="javascript:history.back()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 text-slate-700
                               text-sm font-semibold rounded-xl hover:bg-slate-200 active:scale-95 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Quay lại
                    </a>

                    @if($students->count() > 0)
                    {{-- Tải PDF --}}
                    <button @click="downloadPdf()" :disabled="downloading" type="button"
                        class="inline-flex items-center gap-2 px-5 py-2.5
                               bg-emerald-600 text-white text-sm font-semibold rounded-xl
                               hover:bg-emerald-700 active:scale-95 transition-all shadow-sm
                               disabled:opacity-60 disabled:cursor-wait">
                        <template x-if="!downloading">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </template>
                        <template x-if="downloading">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                            </svg>
                        </template>
                        <span x-text="downloading ? 'Đang tạo PDF...' : 'Tải PDF'"></span>
                    </button>

                    {{-- In trực tiếp --}}
                    <button wire:click="printCards" type="button"
                        class="inline-flex items-center gap-2 px-5 py-2.5
                               bg-gradient-to-r from-blue-600 to-blue-700
                               text-white text-sm font-semibold rounded-xl
                               hover:from-blue-700 hover:to-blue-800
                               active:scale-95 transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        In {{ $students->count() }} thẻ
                    </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Preview --}}
        @if($students->count() > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide">
                    Preview · 2 thẻ / hàng · Kích thước thực 90×58mm
                </p>
                <span class="text-xs text-slate-400">
                    Cuộn xuống để xem tất cả {{ $students->count() }} thẻ
                </span>
            </div>

            {{-- 2 columns preview --}}
            <div class="preview-grid">
                @foreach($students as $student)
                @include('livewire.student.student-card', ['student' => $student, 'lop' => $lop])
                @endforeach
            </div>
        </div>
        @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-12 text-center">
            <svg class="mx-auto w-16 h-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <p class="mt-4 text-slate-500 font-medium">Không có học sinh nào để in</p>
            <p class="text-sm text-slate-400 mt-1">
                Truyền <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-600">?ids=1,2,3</code>
                hoặc <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-600">?classId=X</code>
            </p>
        </div>
        @endif

    </div>{{-- /print:hidden --}}


    {{-- ══ Vùng in / xuất PDF ══ --}}
    @if($students->count() > 0)
    <div id="print-area"
        style="display:none; width: 794px; padding: 38px; box-sizing: border-box;">
        {{-- @foreach($students as $student)
            @include('livewire.student.student-card', ['student' => $student, 'lop' => $lop, 'forPrint' => true])
            @endforeach --}}

        @foreach($students->chunk(8) as $chunk)
        <div class="print-page">
            <div class="print-grid">
                @foreach($chunk as $student)
                @include('livewire.student.student-card', ['student' => $student, 'lop' => $lop, 'forPrint' => true])
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
        area.style.display = 'block';
        window.print();
        setTimeout(() => area.style.display = '', 1000);
    });
</script>

<style>
    /* ══════════════════════════════════════
       PREVIEW — hiển thị trên màn hình
    ══════════════════════════════════════ */

    .preview-grid {
        display: grid;
        grid-template-columns: repeat(2, max-content);
        gap: 16px;
        justify-content: center;
    }

    /* Màn hình nhỏ: 1 cột, scale xuống */
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


    /* ══════════════════════════════════════
       PRINT GRID — dùng cho in và tải PDF
       ─────────────────────────────────────
       A4 rộng 210mm, lề 10mm mỗi bên
       → vùng nội dung = 190mm
       → 2 thẻ CR80 (85.6mm) + gap 6mm = 177.2mm ✓
       → mỗi trang chứa 4 hàng × 2 cột = 8 thẻ
    ══════════════════════════════════════ */

    .print-grid {
        display: grid;
        grid-template-columns: 323px 323px;
        /* 85.6mm × 3.78 ≈ 323px */
        gap: 23px;
        /* 6mm × 3.78 ≈ 23px */
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


    /* ══════════════════════════════════════
       PRINT MEDIA
    ══════════════════════════════════════ */

    @media print {
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Không cắt đôi thẻ khi sang trang */
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