{{-- Xác nhận bỏ điểm chưa lưu khi đổi lớp hoặc học kỳ --}}
<div
    x-data="{ show: false, action: '', value: '' }"
    x-on:confirm-discard-draft.window="show = true; action = $event.detail.action; value = $event.detail.value">
    <div x-show="show" x-cloak
        class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-[60] p-4"
        role="dialog" aria-modal="true"
        @click.self="show = false"
        @keydown.escape.window="show = false">
        <div class="bg-white/90 backdrop-blur-xl rounded-2xl border border-black/[0.06] shadow-mac
            w-full max-w-sm p-6 space-y-4" @click.stop>
            <h3 class="text-base font-semibold tracking-tight text-slate-900">Bạn có thay đổi chưa lưu</h3>
            <p class="text-sm text-slate-500">
                Nếu tiếp tục, điểm đã nhập nhưng chưa lưu sẽ bị mất.
            </p>
            <div class="flex gap-4 pt-1">
                <x-button type="button" variant="secondary" class="flex-1" @click="show = false">
                    Ở lại
                </x-button>
                <x-button type="button" variant="danger" class="flex-1"
                    @click="show = false; $wire.confirmDiscard(action, value)">
                    Bỏ thay đổi
                </x-button>
            </div>
        </div>
    </div>
</div>
