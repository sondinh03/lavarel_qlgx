@if($errors->any())
<div class="p-4 bg-red-50 border border-red-200 rounded-xl">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <p class="text-sm font-semibold text-red-800 mb-1">Vui lòng kiểm tra lại</p>
            <ul class="text-sm text-red-700 space-y-0.5">
                @foreach($errors->all() as $error)
                <li>· {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif
