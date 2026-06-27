{{--
    Partial: thẻ học sinh CR80 (in / preview)
    Props:
      - $student
      - $lop (nullable)
      - $cardType: permanent | annual
      - $parishName: tên giáo xứ (ParishNew)
      - $forPrint (bool, default false)
--}}
@php
$forPrint = $forPrint ?? false;
$cardType = $cardType ?? 'permanent';
$showClassYear = $cardType === 'annual';
$parishName = trim($parishName ?? '');
$fullName = trim(($student->last_name ?? '') . ' ' . ($student->first_name ?? ''));
$saintName = $student->saint->name ?? '';
$className = $lop->name ?? '';
$yearName = $lop->schoolYear->name ?? '';
$birthday = $student->birthday?->format('d/m/Y') ?? '';
$code = $student->student_code ?? ('HS-' . $student->id);
$qrToken = $student->qr_token ?? '';
$isMale = in_array($student->gender, ['male', 1, '1'], true);
$genderColor = $isMale ? '#2AA14A' : '#be185d';
$genderLabel = $isMale ? 'Nam' : 'Nữ';
@endphp

<div class="student-card"
    data-student-id="{{ $student->id }}"
    data-card-type="{{ $cardType }}"
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

    <div style="
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3mm;
        background: linear-gradient(180deg, #145224 0%, #34C759 50%, #57C37F 100%);
    "></div>

    {{-- Header --}}
    <div style="
        background: linear-gradient(135deg, #0f172a 0%, #145224 55%, #2AA14A 100%);
        padding: 2.5mm 3mm 2mm 5mm;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 2mm;
        flex-shrink: 0;
    ">
        <div style="flex: 1; min-width: 0;">
            <div style="color: #ABE1BF; font-size: 5pt; letter-spacing: 1.5px; text-transform: uppercase; font-weight: 600;">
                Thẻ Học Sinh Giáo Lý
            </div>
            @if($showClassYear && ($className || $yearName))
            <div style="color: #fff; font-size: 6.5pt; font-weight: 700; margin-top: 0.5mm; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ implode(' · ', array_filter([$className, $yearName])) }}
            </div>
            @endif
        </div>

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

    {{-- Body --}}
    <div style="
        flex: 1;
        display: flex;
        gap: 0;
        padding: 2.5mm 3mm 2.5mm 5mm;
        overflow: hidden;
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
                border-radius: 1.5mm;
                overflow: hidden;
                background: #EAF7EF;
                border: 0.5mm solid #ABE1BF;
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
                <svg style="width: 8mm; height: 8mm; color: #57C37F;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    border: 0.3mm solid #D5F0DF;
                    border-radius: 1mm;
                    display: block;
                    flex-shrink: 0;
                "
                alt="QR {{ $code }}" />
            @endif
        </div>

        <div style="width: 0.3mm; background: #e2e8f0; margin: 0 2.5mm; flex-shrink: 0;"></div>

        <div style="flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; gap: 1.5mm;">

            @if($saintName)
            <div style="color: #2AA14A; font-size: 6pt; font-weight: 600; font-style: italic;">
                {{ $saintName }}
            </div>
            @endif

            <div style="
                color: #0f172a;
                font-size: 10pt;
                font-weight: 800;
                line-height: 1.2;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            ">{{ $fullName }}</div>

            <div style="height: 0.3mm; background: linear-gradient(90deg, #34C759, transparent); width: 70%;"></div>

            @if($birthday)
            <div style="display: flex; align-items: center; gap: 1.5mm;">
                <span style="color: #64748b; font-size: 5.5pt;">Sinh ngày</span>
                <span style="color: #1e293b; font-size: 6.5pt; font-weight: 600;">{{ $birthday }}</span>
            </div>
            @endif

            <div style="
                display: inline-flex;
                align-items: center;
                gap: 1.5mm;
                background: #EAF7EF;
                border: 0.3mm solid #ABE1BF;
                border-radius: 1mm;
                padding: 1mm 2mm;
                width: fit-content;
                max-width: 100%;
            ">
                <span style="color: #64748b; font-size: 5pt; text-transform: uppercase; letter-spacing: 0.5px; flex-shrink: 0;">Mã HS</span>
                <span style="color: #2AA14A; font-size: 6.5pt; font-weight: 700; font-family: monospace; letter-spacing: 0.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $code }}</span>
            </div>

        </div>
    </div>

    {{-- Footer: tên giáo xứ (ParishNew) --}}
    <div style="
        background: linear-gradient(135deg, #0f172a, #145224);
        padding: 1.5mm 5mm;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        min-height: 5mm;
    ">
        <span style="
            color: #EAF7EF;
            font-size: 5.5pt;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        ">{{ $parishName ?: 'Giáo Xứ' }}</span>
    </div>
</div>
