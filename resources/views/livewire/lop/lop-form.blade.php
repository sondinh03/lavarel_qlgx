<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-4xl space-y-5">

        {{-- Skip link --}}
        <a href="#main-content" class="sr-only focus:not-sr-only">Bỏ qua tới nội dung</a>

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('ds-lop')],
            ['label' => 'Quản lý lớp học', 'url' => route('ds-lop')],
            ['label' => $isEdit ? 'Chỉnh sửa lớp' : 'Tạo lớp mới']
        ]" />

        {{-- Toast --}}
        @foreach (['success', 'message'] as $key)
        @if (session()->has($key))
        <x-toast-notification type="success" :duration="3000">
            {{ session($key) }}
        </x-toast-notification>
        @endif
        @endforeach

        @if (session()->has('error'))
        <x-toast-notification type="error" :duration="4000">
            {{ session('error') }}
        </x-toast-notification>
        @endif

        {{-- Page Header (PRIMARY) --}}
        <x-page-header
            :title="$isEdit ? 'Chỉnh sửa lớp học' : 'Tạo lớp học mới'"
            :description="$isEdit ? 'Cập nhật thông tin lớp học' : 'Thêm lớp học mới vào hệ thống'"
            icon="class"
            gradient="primary" />

        {{-- FORM --}}
        <form wire:submit.prevent="save" id="main-content">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

                {{-- ===== Thông tin cơ bản ===== --}}
                <div class="p-6">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Thông tin cơ bản
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Mã lớp --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Mã lớp <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                wire:model.defer="form.symbol"
                                placeholder="VD: GL-01"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                          focus:outline-none focus:ring-2 focus:ring-primary-500
                                          transition-all
                                          @error('form.symbol') border-red-300 @enderror">
                            @error('form.symbol')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tên lớp --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Tên lớp <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                wire:model.defer="form.name"
                                placeholder="VD: Giáo lý 1"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                          focus:outline-none focus:ring-2 focus:ring-primary-500
                                          transition-all
                                          @error('form.name') border-red-300 @enderror">
                            @error('form.name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Năm học --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Năm học <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="form.schoolyear"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                           focus:outline-none focus:ring-2 focus:ring-primary-500
                                           transition-all
                                           @error('form.schoolyear') border-red-300 @enderror">
                                <option value="">-- Chọn năm học --</option>
                                @foreach($schoolyears as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('form.schoolyear')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Khối --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Khối <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="form.block"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                           focus:outline-none focus:ring-2 focus:ring-primary-500
                                           transition-all
                                           @error('form.block') border-red-300 @enderror">
                                <option value="">-- Chọn khối --</option>
                                @foreach($blocks as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('form.block')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- ===== Phân công giáo lý viên ===== --}}
                <div class="p-6 border-t border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M7 20H2v-2a3 3 0 015.356-1.857" />
                        </svg>
                        Phân công giáo lý viên
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Giáo lý viên chủ nhiệm
                            </label>
                            <select wire:model.defer="form.mainTeacherId"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                           focus:outline-none focus:ring-2 focus:ring-primary-500
                                           transition-all">
                                <option value="">-- Chọn giáo lý viên --</option>
                                @foreach($teachers as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">
                                Giáo lý viên phụ trách
                            </label>
                            <select multiple size="5"
                                wire:model.defer="form.assistantTeacherIds"
                                class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                           focus:outline-none focus:ring-2 focus:ring-primary-500
                                           transition-all">
                                @foreach($teachers as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">
                                Giữ Ctrl (Windows) hoặc Cmd (Mac) để chọn nhiều
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ===== Ghi chú ===== --}}
                <div class="p-6 border-t border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 8h10M7 12h4" />
                        </svg>
                        Ghi chú
                    </h3>

                    <textarea rows="4"
                        wire:model.defer="form.note"
                        placeholder="Nhập ghi chú (không bắt buộc)..."
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl
                                     focus:outline-none focus:ring-2 focus:ring-primary-500
                                     transition-all resize-none"></textarea>
                </div>

                {{-- ===== Footer ===== --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                    <div class="flex flex-col sm:flex-row justify-end gap-3">
                        <a href="{{ route('ds-lop') }}"
                            class="px-6 py-2.5 bg-white border border-slate-300 rounded-xl
                                  text-slate-700 font-semibold hover:bg-slate-100
                                  active:scale-95 transition-all text-center">
                            Huỷ
                        </a>

                        <button type="submit"
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 rounded-xl
                                       bg-primary-600 text-white font-semibold
                                       hover:bg-primary-700
                                       active:scale-[0.98] transition-all
                                       disabled:opacity-60">
                            {{ $isEdit ? 'Cập nhật lớp học' : 'Tạo lớp học' }}
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>


@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush