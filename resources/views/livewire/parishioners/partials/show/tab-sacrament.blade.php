<div class="bg-slate-50 rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-slate-800">Bí tích</h3>
    </div>
    <div class="p-4">
        @livewire('parishioners.sacraments-manager', ['parishionerId' => $parishioner->id], key('sacraments-' . $parishioner->id))
    </div>
</div>
