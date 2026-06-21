<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
    <x-parishioner-section-card title="Phân cấp giáo hội" edit-action="openEditParish">
        <x-info-row label="Giáo phận" :value="$parishioner->diocese?->name" />
        <x-info-row label="Giáo hạt" :value="$parishioner->deanery?->name" />
        <x-info-row label="Giáo xứ" :value="$parishioner->parish?->name" />
        <x-info-row label="Giáo họ" :value="$parishioner->parishGroup?->name" />
        <x-info-row label="Cấp bậc" :value="config('parishioner.level.' . $parishioner->level)" />
    </x-parishioner-section-card>

    <x-parishioner-section-card title="Gia nhập / chuyển xứ" edit-action="openEditParish">
        <x-info-row label="Ngày gia nhập" :value="$parishioner->joined_date?->format('d/m/Y')" />
        <x-info-row label="Chuyển từ xứ" :value="$parishioner->transferredFromParish?->name" />
        <x-info-row label="Ngày chuyển đến" :value="$parishioner->transferred_date?->format('d/m/Y')" />
        <x-info-row label="Lý do rời xứ" :value="$parishioner->left_reason" />
    </x-parishioner-section-card>
</div>
