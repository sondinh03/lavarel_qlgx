<div>
    {{-- Toast Notifications --}}
    <div role="status" aria-live="polite">
        @if (session()->has('message'))
        <x-toast-notification type="success" :duration="3500">
            {{ session('message') }}
        </x-toast-notification>
        @endif

        @if (session()->has('error'))
        <x-toast-notification type="error" :duration="4000">
            {{ session('error') }}
        </x-toast-notification>
        @endif

        @if (session()->has('warning'))
        <x-toast-notification type="warning" :duration="4000">
            {{ session('warning') }}
        </x-toast-notification>
        @endif
    </div>

    {{-- Section: Danh sách GLV hiện tại --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-bold text-slate-900">
                    Giáo lý viên phụ trách
                </h3>
                <p class="text-sm text-slate-600 mt-1">
                    Lớp: <span class="font-semibold">{{ $lop->name }}</span> -
                    {{ $lop->schoolYear->name ?? 'N/A' }}
                </p>
            </div>

            <x-action-button wire="openModal" icon="plus" size="sm">
                Thêm GLV
            </x-action-button>
        </div>

        @if($currentTeachers && $currentTeachers->isNotEmpty())
        <div class="space-y-3">
            @foreach($currentTeachers as $ct)
            <div class="flex items-center justify-between p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition">
                <div class="flex items-center gap-3 flex-1">
                    {{-- Avatar --}}
                    <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 
                                    flex items-center justify-center font-semibold flex-shrink-0">
                        {{ strtoupper(substr($ct['teacher_name'], 0, 2)) }}
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-slate-900">
                                {{ $ct['teacher_name'] }}
                            </p>

                            {{-- Role Badge --}}
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                             {{ $ct['role'] === 1 
                                                ? 'bg-blue-100 text-blue-700' 
                                                : 'bg-purple-100 text-purple-700' }}">
                                {{ $ct['role_label'] }}
                            </span>
                        </div>

                        @if($ct['phone'])
                        <p class="text-sm text-slate-500 mt-0.5">
                            <svg class="inline w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            {{ $ct['phone'] }}
                        </p>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    {{-- Change Role Button --}}
                    <button
                        wire:click="changeRole({{ $ct['id'] }}, {{ $ct['role'] === 1 ? 2 : 1 }})"
                        class="p-2 text-blue-600 hover:text-blue-700 hover:bg-blue-50 
                                   rounded-lg transition"
                        title="{{ $ct['role'] === 1 ? 'Đổi thành Phụ trách' : 'Đổi thành Chủ nhiệm' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </button>

                    {{-- Remove Button --}}
                    <button
                        wire:click="remove({{ $ct['id'] }})"
                        onclick="return confirm('Xóa GLV khỏi lớp?')"
                        class="p-2 text-red-600 hover:text-red-700 hover:bg-red-50 
                                   rounded-lg transition"
                        title="Xóa khỏi lớp">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <x-empty-state
            icon="users"
            title="Chưa có GLV"
            description="Hãy thêm Giáo lý viên cho lớp này">
            <x-action-button wire="openModal" icon="plus">
                Thêm GLV
            </x-action-button>
        </x-empty-state>
        @endif
    </div>

    {{-- Modal: Phân công GLV --}}
    @if($showModal)
    <div
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
        role="dialog"
        aria-modal="true"
        wire:click="closeModal">
        <div
            class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-hidden flex flex-col"
            wire:click.stop>

            {{-- Header --}}
            <div class="flex-shrink-0 p-6 border-b border-slate-200 bg-gradient-to-br from-primary-50 to-white">
                <h2 class="text-xl font-bold text-slate-900">
                    Phân công Giáo lý viên
                </h2>
                <p class="text-sm text-slate-600 mt-1">
                    Lớp: <span class="font-semibold">{{ $lop->name }}</span>
                </p>
            </div>

            {{-- Body --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                {{-- Error Summary --}}
                @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-red-800 mb-2">
                                Vui lòng kiểm tra lại thông tin
                            </h4>
                            <ul class="space-y-1 text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                <li class="flex items-start gap-2">
                                    <span class="text-red-400 font-bold">•</span>
                                    <span>{{ $error }}</span>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Role Selection --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Vai trò <span class="text-red-500">*</span>
                    </label>

                    <div class="grid grid-cols-2 gap-3">
                        {{-- ✅ Chủ nhiệm --}}
                        <div
                            wire:click="$set('selectedRole', 1)"
                            class="relative flex items-center p-3 border-2 rounded-xl cursor-pointer transition
                   {{ $selectedRole === 1 ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">

                            {{-- Hidden Radio Input --}}
                            <input
                                type="radio"
                                wire:model="selectedRole"
                                value="1"
                                id="role-chu-nhiem"
                                class="absolute opacity-0 pointer-events-none">

                            {{-- Visual Content --}}
                            <div class="flex items-center gap-2 w-full">
                                {{-- Custom Radio Button --}}
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0
                            {{ $selectedRole === 1 ? 'border-blue-500 bg-blue-500' : 'border-slate-300' }}">
                                    @if($selectedRole === 1)
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 12 12">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3L4.5 8.5L2 6" />
                                    </svg>
                                    @endif
                                </div>

                                {{-- Label --}}
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900">Chủ nhiệm</div>
                                    <div class="text-xs text-slate-500">Phụ trách chính</div>
                                </div>
                            </div>
                        </div>

                        {{-- ✅ Phụ trách --}}
                        <div
                            wire:click="$set('selectedRole', 2)"
                            class="relative flex items-center p-3 border-2 rounded-xl cursor-pointer transition
                   {{ $selectedRole === 2 ? 'border-purple-500 bg-purple-50' : 'border-slate-200 hover:border-slate-300 hover:bg-slate-50' }}">

                            {{-- Hidden Radio Input --}}
                            <input
                                type="radio"
                                wire:model="selectedRole"
                                value="2"
                                id="role-pho"
                                class="absolute opacity-0 pointer-events-none">

                            {{-- Visual Content --}}
                            <div class="flex items-center gap-2 w-full">
                                {{-- Custom Radio Button --}}
                                <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0
                            {{ $selectedRole === 2 ? 'border-purple-500 bg-purple-500' : 'border-slate-300' }}">
                                    @if($selectedRole === 2)
                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 12 12">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3L4.5 8.5L2 6" />
                                    </svg>
                                    @endif
                                </div>

                                {{-- Label --}}
                                <div class="flex-1">
                                    <div class="font-semibold text-slate-900">Phụ trách</div>
                                    <div class="text-xs text-slate-500">Hỗ trợ</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Error message --}}
                    @error('selectedRole')
                    <p class="mt-2 text-sm text-red-500 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                {{-- Search input --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Tìm kiếm Giáo lý viên
                    </label>
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.debounce.300ms="teacherSearch"
                            placeholder="Nhập tên hoặc số điện thoại..."
                            class="w-full pl-10 pr-3 py-2 rounded-xl border border-slate-300
                                   focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <svg class="absolute left-3 top-2.5 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                {{-- Select teacher --}}
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Chọn Giáo lý viên <span class="text-red-500">*</span>
                    </label>

                    @if($availableTeachers->isNotEmpty())
                    <div class="border border-slate-200 rounded-xl max-h-60 overflow-y-auto">
                        @foreach($availableTeachers as $teacher)
                        <label
                            class="flex items-center gap-3 p-3 hover:bg-slate-50 cursor-pointer transition
                                       {{ $selectedTeacherId === $teacher->id ? 'bg-primary-50' : '' }}">
                            <input
                                type="radio"
                                wire:model="selectedTeacherId"
                                value="{{ $teacher->id }}"
                                class="w-4 h-4 text-primary-600 focus:ring-primary-500">

                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-slate-900 truncate">{{ $teacher->name }}</p>
                                <div class="flex items-center gap-3 text-sm text-slate-500 mt-0.5">
                                    @if($teacher->phone_number)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        {{ $teacher->phone_number }}
                                    </span>
                                    @endif
                                    @if($teacher->position)
                                    <span class="text-xs px-2 py-0.5 bg-slate-100 rounded-full">
                                        {{ $teacher->position }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8 border border-slate-200 rounded-xl">
                        <svg class="mx-auto w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="mt-2 text-slate-500">
                            {{ empty($teacherSearch) ? 'Không có GLV nào' : 'Không tìm thấy kết quả' }}
                        </p>
                    </div>
                    @endif

                    @error('selectedTeacherId')
                    <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex-shrink-0 px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end gap-3">
                <x-action-button wire="closeModal" variant="secondary">
                    Hủy
                </x-action-button>

                <x-action-button
                    wire="assign"
                    icon="check"
                    :loading="true"
                    :disabled="!$selectedTeacherId">
                    Phân công
                </x-action-button>
            </div>
        </div>
    </div>
    @endif
</div>