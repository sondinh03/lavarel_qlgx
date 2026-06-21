<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
        <input type="checkbox" wire:model.defer="status" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
        <span class="text-sm text-slate-700">Đang hoạt động</span>
    </label>
    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
        <input type="checkbox" wire:model.defer="is_active" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
        <span class="text-sm text-slate-700">Sinh hoạt tại xứ</span>
    </label>
    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
        <input type="checkbox" wire:model.defer="is_new_convert" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
        <span class="text-sm text-slate-700">Tân tòng</span>
    </label>
    <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer">
        <input type="checkbox" wire:model.defer="is_included_in_stats" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500" />
        <span class="text-sm text-slate-700">Được thống kê</span>
    </label>
</div>
