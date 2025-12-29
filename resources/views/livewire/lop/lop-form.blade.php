<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-4xl space-y-5">

        {{-- Breadcrumb --}}
        <x-breadcrumb :items="[
            ['label' => 'Trang chủ', 'url' => route('ds-lop')],
            ['label' => 'Quản lý lớp học', 'url' => route('ds-lop')],
            ['label' => $isEdit ? 'Chỉnh sửa lớp' : 'Tạo lớp mới']
        ]" />

        {{-- Toast Notifications --}}
        @if (session()->has('success'))
        <x-toast-notification type="success" :duration="3000">
            {{ session('success') }}
        </x-toast-notification>
        @endif

        @if (session()->has('message'))
        <x-toast-notification type="success" :duration="3000">
            {{ session('message') }}
        </x-toast-notification>
        @endif

        @if (session()->has('error'))
        <x-toast-notification type="error" :duration="4000">
            {{ session('error') }}
        </x-toast-notification>
        @endif

        {{-- Header --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <x-page-header
                :title="$isEdit ? 'Chỉnh sửa lớp học' : 'Tạo lớp học mới'"
                :description="$isEdit ? 'Cập nhật thông tin lớp học' : 'Thêm lớp học mới vào hệ thống'"
                icon="class"
                gradient="purple" />
        </div>

        {{-- Form Card --}}
        <form wire:submit.prevent="save">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 space-y-6">

                    {{-- Basic Information Section --}}
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Thông tin cơ bản
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Mã lớp --}}
                            <div>
                                <label for="symbol" class="block text-sm font-semibold text-slate-700 mb-2">
                                    Mã lớp <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="symbol"
                                    wire:model.defer="form.symbol"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('form.symbol') border-red-300 @enderror"
                                    placeholder="VD: GL-01">
                                @error('form.symbol')
                                <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            {{-- Tên lớp --}}
                            <div>
                                <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                                    Tên lớp <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    wire:model.defer="form.name"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('form.name') border-red-300 @enderror"
                                    placeholder="VD: Giáo lý 1">
                                @error('form.name')
                                <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            {{-- Năm học --}}
                            <div>
                                <label for="schoolyear" class="block text-sm font-semibold text-slate-700 mb-2">
                                    Năm học <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="schoolyear"
                                    wire:model="form.schoolyear"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('form.schoolyear') border-red-300 @enderror">
                                    <option value="">-- Chọn năm học --</option>
                                    @foreach($schoolyears as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>

                                @error('form.schoolyear')
                                <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            {{-- Khối --}}
                            <div>
                                <label for="block" class="block text-sm font-semibold text-slate-700 mb-2">
                                    Khối <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="block"
                                    wire:model="form.block"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all @error('form.block') border-red-300 @enderror">
                                    <option value="">-- Chọn khối --</option>
                                    @foreach($blocks as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>


                                @error('form.block')
                                <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>

                            {{--
                            <div class="p-4 bg-slate-50 rounded-xl">
                                @livewire('class-filter-selector', [
                                'parish_id' => $parish_id,
                                'selectedNamHoc' => $form['schoolyear'],
                                'selectedKhoi' => $form['block'],
                                'showLop' => false, 
                            ])
                        </div>
                        --}}
                        </div>
                    </div>

                    {{-- Teacher Assignment Section --}}
                    <div class="border-t border-slate-200 pt-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Phân công giáo lý viên
                        </h3>

                        <div class="space-y-3">
                            {{-- Giáo lý viên chủ nhiệm --}}
                            <div>
                                <label for="main_teacher" class="block text-sm font-semibold text-slate-700 mb-2">
                                    Giáo lý viên chủ nhiệm
                                </label>
                                <select
                                    id="main_teacher"
                                    wire:model.defer="form.main_teacher_id"
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                    <option value="">-- Chọn giáo lý viên --</option>
                                    @foreach($teachers as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Giáo lý viên phụ trách --}}
                            <div>
                                <label for="assistant_teachers" class="block text-sm font-semibold text-slate-700 mb-2">
                                    Giáo lý viên phụ trách (có thể chọn nhiều)
                                </label>
                                <select
                                    id="assistant_teachers"
                                    wire:model.defer="form.assistant_teacher_ids"
                                    multiple
                                    class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    size="5">
                                    @foreach($teachers as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-xs text-slate-500">Giữ Ctrl (Windows) hoặc Cmd (Mac) để chọn nhiều giáo lý viên</p>
                            </div>
                        </div>
                    </div>

                    {{-- Note Section --}}
                    <div class="border-t border-slate-200 pt-6">
                        <h3 class="text-lg font-bold text-slate-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            Ghi chú
                        </h3>

                        <div>
                            <label for="note" class="sr-only">Ghi chú về lớp học</label>
                            <textarea
                                id="note"
                                wire:model.defer="form.note"
                                rows="4"
                                class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:border-transparent transition-all resize-none"
                                placeholder="Nhập ghi chú về lớp học (không bắt buộc)..."></textarea>
                        </div>
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
                    <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                        <a href="{{ route('ds-lop') }}"
                            class="w-full sm:w-auto px-6 py-2.5 bg-white border border-slate-200 rounded-xl text-slate-700 font-semibold hover:bg-slate-50 active:scale-95 transition-all text-center">
                            Hủy
                        </a>
                        <button
                            type="submit"
                            class="w-full sm:w-auto px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-semibold hover:from-purple-700 hover:to-indigo-700 active:scale-95 transition-all shadow-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled"
                            wire:target="save">
                            <svg wire:loading.remove wire:target="save" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg wire:loading wire:target="save" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="save">
                                {{ $isEdit ? 'Cập nhật lớp học' : 'Tạo lớp học' }}
                            </span>
                            <span wire:loading wire:target="save">
                                Đang lưu...
                            </span>
                        </button>
                        @if (session()->has('success'))
                        <x-toast-notification type="success" :duration="3000">
                            {{ session('success') }}
                        </x-toast-notification>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush