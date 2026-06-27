@section('title', 'Đăng ký sổ gia đình')

<div class="min-h-screen py-4 sm:py-8">
    <div class="mx-auto max-w-lg space-y-5">

        <nav class="flex flex-wrap items-center gap-x-4 gap-y-1 px-1">
            <a href="{{ route('landing') }}"
                class="text-sm text-slate-500 hover:text-primary-600 transition">
                ← Về trang chủ
            </a>
        </nav>

        <div class="text-center px-2">
            <img src="{{ url(config('settings.logo')) }}" class="h-16 w-auto mx-auto mb-3" alt="">
            <h1 class="text-2xl font-bold text-slate-900">Khai báo sổ gia đình</h1>
            <p class="mt-2 text-sm text-slate-600">
                Nhập đầy đủ thông tin hộ gia đình, thành viên (kèm bí tích) và hôn phối theo sổ gia đình công giáo.
            </p>
        </div>

        @if($submitted)
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-200 p-6 text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">Đã gửi yêu cầu thành công</h2>
                <p class="text-sm text-slate-600 mt-2">Mã gia đình / mã theo dõi:</p>
                <p class="text-xl font-mono font-bold text-primary-600 mt-1">{{ $referenceCode }}</p>
            </div>
            <p class="text-sm text-slate-500">Quản trị viên sẽ duyệt và tạo hồ sơ gia đình trong hệ thống.</p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('landing') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700">
                    Về trang chủ
                </a>
            </div>
        </div>
        @else
        <form wire:submit.prevent="submit" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 overflow-x-auto">
                <div class="flex gap-2 text-xs font-medium min-w-max">
                    @foreach($stepLabels as $step => $label)
                    <button type="button" wire:click="goToStep('{{ $step }}')"
                        class="px-3 py-1.5 rounded-lg transition whitespace-nowrap
                            {{ $activeStep === $step ? 'bg-primary-600 text-white' : 'bg-white text-slate-600 border border-slate-200' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            <div class="p-4 sm:p-5 space-y-5">
                @error('submit') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

                @if($activeStep === 'household')
                    @include('livewire.parishioners.partials.family-register-step-household')
                @elseif($activeStep === 'members')
                    @include('livewire.parishioners.partials.family-register-step-members')
                @elseif($activeStep === 'marriages')
                    @include('livewire.parishioners.partials.family-register-step-marriages')
                @elseif($activeStep === 'contact')
                    @include('livewire.parishioners.partials.family-register-step-contact')
                @endif
            </div>

            <div class="px-4 sm:px-5 py-4 border-t border-slate-200 bg-slate-50 flex flex-wrap gap-3 justify-between">
                @if($activeStep !== 'household')
                <button type="button" wire:click="prevStep"
                    class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-medium text-slate-700 bg-white">
                    Quay lại
                </button>
                @else
                <span></span>
                @endif

                @if($activeStep === 'contact')
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700 disabled:opacity-50"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Gửi đăng ký</span>
                    <span wire:loading wire:target="submit">Đang gửi...</span>
                </button>
                @else
                <button type="button" wire:click="nextStep"
                    class="px-5 py-2.5 rounded-xl bg-primary-600 text-white text-sm font-semibold hover:bg-primary-700">
                    Tiếp theo
                </button>
                @endif
            </div>
        </form>
        @endif
    </div>
</div>
