@php $input = "w-full px-3 py-2 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp



<div class="space-y-4">

    <div class="p-4 rounded-xl border {{ $announcements_one_done ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-white' }}">

        <div class="flex items-start justify-between gap-3 mb-3">

            <p class="text-sm font-semibold text-slate-800">Lần rao 1</p>

            @if($announcements_one_done)

            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Đã rao</span>

            @endif

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">

            <div>

                <label class="block text-sm font-medium text-slate-700 mb-1">Ngày rao</label>

                <input wire:model.lazy="announcements_one" type="date" class="{{ $input }}" />

                @error('announcements_one') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

            </div>

            <div>

                <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer select-none">

                    <input type="checkbox" wire:model="announcements_one_done" @if(! $announcements_one) disabled @endif class="rounded border-slate-300 text-primary-600 disabled:opacity-40" />

                    <span>Đã qua đợt rao này</span>

                </label>

                @error('announcements_one_done') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                @if(! $announcements_one)

                <p class="text-xs text-slate-400 mt-1">Nhập ngày rao trước khi đánh dấu.</p>

                @endif

            </div>

        </div>

    </div>



    <div class="p-4 rounded-xl border {{ $announcements_two_done ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-white' }}">

        <div class="flex items-start justify-between gap-3 mb-3">

            <p class="text-sm font-semibold text-slate-800">Lần rao 2</p>

            @if($announcements_two_done)

            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Đã rao</span>

            @endif

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">

            <div>

                <label class="block text-sm font-medium text-slate-700 mb-1">Ngày rao</label>

                <input wire:model.lazy="announcements_two" type="date" class="{{ $input }}" />

                @if($this->suggestedDateTwo)

                <p class="text-xs text-slate-400 mt-1">Gợi ý: {{ \Carbon\Carbon::parse($this->suggestedDateTwo)->format('d/m/Y') }}</p>

                @endif

                @error('announcements_two') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

            </div>

            <div>

                <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer select-none">

                    <input type="checkbox" wire:model="announcements_two_done" @if(! $announcements_two) disabled @endif class="rounded border-slate-300 text-primary-600 disabled:opacity-40" />

                    <span>Đã qua đợt rao này</span>

                </label>

                @error('announcements_two_done') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                @if(! $announcements_two)

                <p class="text-xs text-slate-400 mt-1">Nhập ngày rao trước khi đánh dấu.</p>

                @endif

            </div>

        </div>

    </div>



    <div class="p-4 rounded-xl border {{ $announcements_three_done ? 'border-emerald-200 bg-emerald-50/40' : 'border-slate-200 bg-white' }}">

        <div class="flex items-start justify-between gap-3 mb-3">

            <p class="text-sm font-semibold text-slate-800">Lần rao 3</p>

            @if($announcements_three_done)

            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Đã rao</span>

            @endif

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end">

            <div>

                <label class="block text-sm font-medium text-slate-700 mb-1">Ngày rao</label>

                <input wire:model.lazy="announcements_three" type="date" class="{{ $input }}" />

                @if($this->suggestedDateThree)

                <p class="text-xs text-slate-400 mt-1">Gợi ý: {{ \Carbon\Carbon::parse($this->suggestedDateThree)->format('d/m/Y') }}</p>

                @endif

                @error('announcements_three') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

            </div>

            <div>

                <label class="inline-flex items-center gap-2 text-sm text-slate-700 cursor-pointer select-none">

                    <input type="checkbox" wire:model="announcements_three_done" @if(! $announcements_three) disabled @endif class="rounded border-slate-300 text-primary-600 disabled:opacity-40" />

                    <span>Đã qua đợt rao này</span>

                </label>

                @error('announcements_three_done') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror

                @if(! $announcements_three)

                <p class="text-xs text-slate-400 mt-1">Nhập ngày rao trước khi đánh dấu.</p>

                @endif

            </div>

        </div>

    </div>

</div>



<p class="text-xs text-slate-500 mt-3">

    Mỗi lần rao cách nhau tối thiểu {{ config('marriage-announcement.min_days_between_announcements') }} ngày.

    Hồ sơ chỉ chuyển sang <strong>Hoàn thành</strong> khi đã đánh dấu đủ cả 3 đợt rao.

</p>


