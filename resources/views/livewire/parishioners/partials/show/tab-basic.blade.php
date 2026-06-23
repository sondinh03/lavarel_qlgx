@php $roleLabels = ['husband' => 'Chồng', 'wife' => 'Vợ', 'child' => 'Con', 'other' => 'Khác']; @endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
    <x-parishioner-section-card title="Nhân thân" edit-action="openEditBasic">
        <x-info-row label="Họ và tên" :value="$parishioner->full_name" />
        <x-info-row label="Tên thánh" :value="$parishioner->saint?->name" />
        <x-info-row label="Giới tính" :value="$parishioner->gender_name" />
        <x-info-row label="Ngày sinh" :value="$parishioner->birthday?->format('d/m/Y')" />
        <x-info-row label="Nơi sinh" :value="$parishioner->birth_place" />
        <x-info-row label="Con thứ" :value="$parishioner->birth_order ? 'Con thứ ' . $parishioner->birth_order : null" />
        <x-info-row label="Tuổi" :value="$parishioner->age ? $parishioner->age . ' tuổi' : null" />
    </x-parishioner-section-card>

    <x-parishioner-section-card title="Liên hệ" edit-action="openEditBasic">
        <x-info-row label="CCCD" :value="$parishioner->cccd" />
        <x-info-row label="Điện thoại" :value="$parishioner->phone" />
        <x-info-row label="Email" :value="$parishioner->email" />
    </x-parishioner-section-card>

    <x-parishioner-section-card title="Phân loại" edit-action="openEditBasic">
        <x-info-row label="Dân tộc" :value="config('parishioner.ethnic.' . $parishioner->ethnic)" />
        <x-info-row label="Nghề nghiệp" :value="config('parishioner.career.' . $parishioner->career)" />
        <x-info-row label="Học vấn" :value="config('parishioner.education_level.' . $parishioner->education_level)" />
        <x-info-row label="Trình độ chuyên môn" :value="config('parishioner.specialist_level.' . $parishioner->specialist_level)" />
        <x-info-row label="Trình độ giáo lý" :value="config('parishioner.catechism_level.' . $parishioner->catechism_level)" />
        <x-info-row label="Chuyên ngành GL" :value="$parishioner->catechism_major" />
        <x-info-row label="Chức vụ" :value="config('parishioner.position.' . $parishioner->position)" />
        <x-info-row label="Ngôn ngữ" :value="config('parishioner.language.' . $parishioner->language)" />
        <x-info-row label="Thánh chức" :value="config('parishioner.holy_order_status.' . $parishioner->holy_order_status)" />
    </x-parishioner-section-card>

    <x-parishioner-section-card title="Trạng thái" edit-action="openEditBasic">
        <x-info-row label="Kích hoạt" :value="$parishioner->status ? 'Có' : 'Không'" />
        <x-info-row label="Sinh hoạt tại xứ" :value="$parishioner->is_active ? 'Có' : 'Không'" />
        <x-info-row label="Tân tòng" :value="$parishioner->is_new_convert ? 'Có' : 'Không'" />
        <x-info-row label="Được thống kê" :value="$parishioner->is_included_in_stats ? 'Có' : 'Không'" />
    </x-parishioner-section-card>

    @if($parishioner->student)
    <div class="lg:col-span-2">
        <x-parishioner-section-card title="Học sinh GL">
            <div class="px-4 py-3 flex justify-between gap-4 items-center">
                <span class="text-slate-600">Hồ sơ học sinh liên kết</span>
                <a href="{{ route('students.show', $parishioner->student->id) }}"
                    class="text-sm font-semibold text-primary-600 hover:text-primary-700">
                    {{ $parishioner->student->full_name_with_saint ?? 'Xem học sinh' }} →
                </a>
            </div>
        </x-parishioner-section-card>
    </div>
    @endif

    @if($parishioner->note)
    <div class="lg:col-span-2 p-4 bg-amber-50 border border-amber-100 rounded-2xl text-sm text-slate-700">
        <p class="text-xs font-semibold text-amber-600 mb-1">Ghi chú</p>
        {{ $parishioner->note }}
    </div>
    @endif

    <div class="lg:col-span-2">
        <x-parishioner-section-card title="Quê quán & Địa chỉ" edit-action="openEditAddress">
            <x-info-row label="Quê quán" :value="$parishioner->origin" />
            <x-info-row label="Thường trú" :value="$parishioner->full_address_permanent ?: null" />
            <x-info-row label="Tạm trú" :value="$parishioner->full_address_temporary ?: null" />
        </x-parishioner-section-card>
    </div>
</div>
