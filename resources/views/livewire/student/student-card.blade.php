{{--
    Partial: student-card
    Props:
      - $student  : StudentNew (with saint, parishGroup)
      - $lop      : CatechismClass|null
      - $forPrint : bool (default false)
--}}
@php
$forPrint = $forPrint ?? false;
$fullName = trim(($student->last_name ?? '') . ' ' . ($student->first_name ?? ''));
$saintName = $student->saint->name ?? '';
$className = $lop->name ?? '';
$yearName = $lop->schoolYear->name ?? '';
$birthday = $student->birthday?->format('d/m/Y') ?? '';
$code = $student->student_code ?? ('HS-' . $student->id);
$qrToken = $student->qr_token ?? $code;
$isMale = in_array($student->gender, ['male', 1, '1']);
$genderColor = $isMale ? '#1d4ed8' : '#be185d';
$genderLabel = $isMale ? 'Nam' : 'Nữ';
@endphp

<div class="student-card"
    data-student-id="{{ $student->id }}"
    style="
        width: 85.6mm;
        height: 54mm;
        background: #fff;
        border-radius: 3mm;
        overflow: hidden;
        position: relative;
        box-shadow: 0 1px 4px rgba(0,0,0,0.12);
        display: flex;
        flex-direction: column;
        font-family: 'Segoe UI', Arial, sans-serif;
        flex-shrink: 0;
    ">

    {{-- ── Dải màu bên trái ── --}}
    <div style="
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3mm;
        background: linear-gradient(180deg, #1e40af 0%, #3b82f6 50%, #06b6d4 100%);
    "></div>

    {{-- ── Header ── --}}
    <div style="
        background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #1d4ed8 100%);
        padding: 2.5mm 3mm 2mm 5mm;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2mm;
        flex-shrink: 0;
    ">
        <div style="flex: 1; min-width: 0;">
            <div style="color: #93c5fd; font-size: 5pt; letter-spacing: 1.5px; text-transform: uppercase; font-weight: 600;">
                Thẻ Học Sinh Giáo Lý
            </div>
            @if($className || $yearName)
            <div style="color: #fff; font-size: 6.5pt; font-weight: 700; margin-top: 0.5mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ implode(' · ', array_filter([$className, $yearName])) }}
            </div>
            @endif
        </div>

        {{-- Badge giới tính --}}
        <div style="
            background: {{ $genderColor }};
            color: white;
            font-size: 5.5pt;
            font-weight: 700;
            padding: 1mm 2.5mm;
            border-radius: 10mm;
            flex-shrink: 0;
            letter-spacing: 0.5px;
        ">{{ $genderLabel }}</div>
    </div>

    {{-- ── Body ── --}}
    <div style="
        flex: 1;
        display: flex;
        gap: 0;
        padding: 2.5mm 3mm 2.5mm 5mm;
        overflow: hidden;
    ">
        {{-- Cột trái: Ảnh + QR --}}
        <div style="
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5mm;
            flex-shrink: 0;
            width: 19mm;
        ">
            {{-- Ảnh đại diện --}}
            <div style="
                width: 14mm;
                height: 16mm;
                border-radius: 1.5mm;
                overflow: hidden;
                background: #e2e8f0;
                border: 0.5mm solid #cbd5e1;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            ">
                @if(!empty($student->avatar_path))
                <img src="{{ Storage::url($student->avatar_path) }}"
                    style="width: 100%; height: 100%; object-fit: cover;"
                    alt="{{ $fullName }}" />
                @else
                {{-- Placeholder avatar --}}
                <svg style="width: 8mm; height: 8mm; color: #94a3b8;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                @endif
            </div>

            {{-- QR Code --}}
            <img
                src="https://api.qrserver.com/v1/create-qr-code/?size=53x53&data={{ urlencode($qrToken) }}&color=0f172a&bgcolor=ffffff"
                style="
        width: 14mm;
        height: 14mm;
        border: 0.3mm solid #e2e8f0;
        border-radius: 1mm;
        display: block;
        flex-shrink: 0;
    "
                alt="QR {{ $code }}"
                crossorigin="anonymous" />
        </div>

        {{-- Divider dọc --}}
        <div style="width: 0.3mm; background: #e2e8f0; margin: 0 2.5mm; flex-shrink: 0;"></div>

        {{-- Cột phải: Thông tin --}}
        <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; gap: 1.5mm;">

            {{-- Tên thánh --}}
            @if($saintName)
            <div style="color: #2563eb; font-size: 6pt; font-weight: 600; font-style: italic;">
                {{ $saintName }}
            </div>
            @endif

            {{-- Họ tên --}}
            <div style="
                color: #0f172a;
                font-size: 10pt;
                font-weight: 800;
                line-height: 1.2;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            ">{{ $fullName }}</div>

            {{-- Separator --}}
            <div style="height: 0.3mm; background: linear-gradient(90deg, #3b82f6, transparent); width: 70%;"></div>

            {{-- Ngày sinh --}}
            @if($birthday)
            <div style="display: flex; align-items: center; gap: 1.5mm;">
                <span style="color: #64748b; font-size: 5.5pt;">Sinh ngày</span>
                <span style="color: #1e293b; font-size: 6.5pt; font-weight: 600;">{{ $birthday }}</span>
            </div>
            @endif

            {{-- Mã học sinh --}}
            <div style="
                display: inline-flex;
                align-items: center;
                gap: 1.5mm;
                background: #eff6ff;
                border: 0.3mm solid #bfdbfe;
                border-radius: 1mm;
                padding: 1mm 2mm;
                width: fit-content;
            ">
                <span style="color: #64748b; font-size: 5pt; text-transform: uppercase; letter-spacing: 0.5px;">Mã HS</span>
                <span style="color: #1d4ed8; font-size: 6pt; font-weight: 700; font-family: monospace; letter-spacing: 0.5px;">{{ $code }}</span>
            </div>

        </div>
    </div>

    {{-- ── Footer ── --}}
    <div style="
        background: linear-gradient(135deg, #0f172a, #1e3a8a);
        padding: 1.5mm 5mm;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-shrink: 0;
    ">
        <span style="color: #60a5fa; font-size: 4.5pt; letter-spacing: 1.5px; text-transform: uppercase; font-weight: 600;">
            Giáo Xứ
        </span>
        <div style="height: 0.3mm; flex: 1; background: rgba(255,255,255,0.15); margin: 0 2mm;"></div>
        <span style="color: #93c5fd; font-size: 4.5pt; letter-spacing: 1px; text-transform: uppercase;">
            {{ $yearName ?: 'Năm Học' }}
        </span>
    </div>

</div>