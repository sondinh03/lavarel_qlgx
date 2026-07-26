{{--
    Partial: thẻ học sinh CR80 (in / preview)
    Props:
      - $student
      - $lop (nullable)
      - $cardType: permanent | annual
      - $parishName: tên giáo xứ (ParishNew)
      - $parishLogoUrl: URL logo giáo xứ (nullable)
      - $colors: palette từ CardTheme::resolve()
      - $forPrint (bool, default false)
--}}
@php
use App\Support\CardTheme;

$forPrint = $forPrint ?? false;
$cardType = $cardType ?? 'permanent';
$showClassYear = $cardType === 'annual';
$parishName = trim($parishName ?? '');
$parishLogoUrl = $parishLogoUrl ?? null;
$colors = $colors ?? CardTheme::resolve(CardTheme::DEFAULT);
$fullName = trim(($student->last_name ?? '') . ' ' . ($student->first_name ?? ''));
$saintName = $student->saint->name ?? '';
$className = $lop->name ?? '';
$yearName = $lop->schoolYear->name ?? '';
$birthday = $student->birthday?->format('d/m/Y') ?? '';
$parishGroup = $student->parishGroup->name ?? '';
$phone = trim((string) ($student->phone ?? ''));
$qrToken = $student->qr_token ?? '';
$isMale = in_array($student->gender, ['male', 1, '1'], true);
$genderLabel = $isMale ? 'Nam' : 'Nữ';
$genderBg = $isMale ? 'rgba(52, 199, 89, 0.14)' : 'rgba(255, 59, 48, 0.12)';
$genderFg = $isMale ? '#1F7A38' : '#C0392B';
$cardShadow = $forPrint ? 'none' : '0 2px 12px rgba(0,0,0,0.06), 0 0 0 0.5px rgba(0,0,0,0.06)';
@endphp

<div class="student-card"
    data-student-id="{{ $student->id }}"
    data-card-type="{{ $cardType }}"
    style="
        width: 85.60mm;
        height: 53.98mm;
        box-sizing: border-box;
        background: #ffffff;
        border-radius: 3mm;
        overflow: hidden;
        position: relative;
        box-shadow: {{ $cardShadow }};
        display: flex;
        flex-direction: column;
        font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Segoe UI', Arial, sans-serif;
        flex-shrink: 0;
        border: 0.3mm solid rgba(0,0,0,0.06);
    ">

    {{-- Accent strip --}}
    <div style="
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 2mm;
        background: linear-gradient(180deg, {{ $colors['light'] }} 0%, {{ $colors['primary'] }} 55%, {{ $colors['dark'] }} 100%);
        z-index: 2;
    "></div>

    {{-- Logo giáo xứ: góc phải, ngang ảnh cá nhân --}}
    @if($parishLogoUrl)
    <div style="
        position: absolute;
        top: 14.5mm;
        right: 3mm;
        width: 14mm;
        height: 14mm;
        border-radius: 50%;
        overflow: hidden;
        opacity: 0.28;
        z-index: 1;
        background: #ffffff;
        border: 0.3mm solid rgba(0,0,0,0.08);
        box-shadow: 0 0 0 0.4mm rgba(255,255,255,0.5);
        pointer-events: none;
    ">
        <img src="{{ $parishLogoUrl }}"
            alt=""
            style="width: 100%; height: 100%; object-fit: cover; display: block;" />
    </div>
    @endif

    {{-- Header: parish name on top --}}
    <div style="
        background: linear-gradient(180deg, {{ $colors['bgFrom'] }} 0%, {{ $colors['bgTo'] }} 100%);
        border-bottom: 0.3mm solid {{ $colors['headerBorder'] }};
        padding: 2mm 3mm 1.8mm 5mm;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2mm;
        flex-shrink: 0;
        position: relative;
        z-index: 2;
    ">
        <div style="flex: 1; min-width: 0;">
            <div style="
                color: {{ $colors['deep'] }};
                font-size: 7pt;
                font-weight: 700;
                letter-spacing: 0.2px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            ">{{ $parishName ?: 'Giáo xứ' }}</div>
            <div style="color: #64748b; font-size: 5pt; letter-spacing: 1px; text-transform: uppercase; font-weight: 600; margin-top: 0.4mm;">
                Thẻ Học Sinh Giáo Lý
                @if($showClassYear && ($className || $yearName))
                · {{ implode(' · ', array_filter([$className, $yearName])) }}
                @endif
            </div>
        </div>

        <div style="
            background: {{ $genderBg }};
            color: {{ $genderFg }};
            font-size: 5.5pt;
            font-weight: 650;
            padding: 1mm 2.5mm;
            border-radius: 10mm;
            flex-shrink: 0;
            letter-spacing: 0.3px;
        ">{{ $genderLabel }}</div>
    </div>

    {{-- Body --}}
    <div style="
        flex: 1;
        display: flex;
        gap: 0;
        padding: 2.5mm 3mm 2.5mm 5mm;
        overflow: hidden;
        background: #ffffff;
    ">
        <div style="
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5mm;
            flex-shrink: 0;
            width: 19mm;
        ">
            <div style="
                width: 14mm;
                height: 16mm;
                border-radius: 2mm;
                overflow: hidden;
                background: {{ $colors['avatarBg'] }};
                border: 0.4mm solid {{ $colors['ringBorder'] }};
                box-shadow: 0 0 0 0.8mm {{ $colors['ringGlow'] }}, 0 1mm 2mm rgba(0,0,0,0.04);
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            ">
                @if(!empty($student->avatar_path))
                <img src="{{ $student->avatar_url }}"
                    style="width: 100%; height: 100%; object-fit: cover;"
                    alt="{{ $fullName }}" />
                @else
                <svg style="width: 8mm; height: 8mm; color: {{ $colors['light'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                @endif
            </div>

            @if($qrToken)
            <img
                src="{{ route('students.qr-image', ['token' => $qrToken, 'size' => 200]) }}"
                style="
                    width: 14mm;
                    height: 14mm;
                    border: 0.3mm solid rgba(0,0,0,0.06);
                    border-radius: 1.5mm;
                    display: block;
                    flex-shrink: 0;
                    background: #fff;
                "
                alt="QR {{ $fullName }}" />
            @endif
        </div>

        <div style="width: 0.3mm; background: rgba(0,0,0,0.06); margin: 0 2.5mm; flex-shrink: 0;"></div>

        <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; gap: 1.2mm;">

            @if($saintName)
            <div style="color: {{ $colors['dark'] }}; font-size: 6pt; font-weight: 600; font-style: italic;">
                {{ $saintName }}
            </div>
            @endif

            <div style="
                color: #0f172a;
                font-size: 10pt;
                font-weight: 700;
                letter-spacing: -0.2px;
                line-height: 1.15;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            ">{{ $fullName }}</div>

            <div style="height: 0.3mm; background: linear-gradient(90deg, {{ $colors['divider'] }}, transparent); width: 70%;"></div>

            <div style="display: flex; align-items: baseline; gap: 1.5mm; min-width: 0;">
                <span style="color: #64748b; font-size: 5.5pt; flex-shrink: 0;">Sinh ngày</span>
                <span style="color: #1e293b; font-size: 6.5pt; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $birthday !== '' ? $birthday : '—' }}</span>
            </div>

            <div style="display: flex; align-items: baseline; gap: 1.5mm; min-width: 0;">
                <span style="color: #64748b; font-size: 5.5pt; flex-shrink: 0;">Giáo họ</span>
                <span style="color: #1e293b; font-size: 6.5pt; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $parishGroup !== '' ? $parishGroup : '—' }}</span>
            </div>

            <div style="display: flex; align-items: baseline; gap: 1.5mm; min-width: 0;">
                <span style="color: #64748b; font-size: 5.5pt; flex-shrink: 0;">Liên hệ</span>
                <span style="color: #1e293b; font-size: 6.5pt; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $phone !== '' ? $phone : '—' }}</span>
            </div>

        </div>
    </div>
</div>
