@php $input = "w-full px-3 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"; @endphp

<div class="space-y-4">
  @if(count($members) === 1)
  <p class="text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
    Vui lòng bấm <strong>Sửa</strong> để nhập đầy đủ thông tin <strong>người đăng ký</strong> (thành viên đầu tiên) trước khi thêm thành viên thứ hai.
  </p>
  @endif

  @foreach($members as $index => $member)
  <div class="rounded-xl border border-slate-200 p-3 {{ ($member['ref'] ?? '') === $submitter_ref ? 'ring-2 ring-primary-200' : '' }}">
    <div class="flex items-start justify-between gap-2">
      <div>
        <p class="font-semibold text-slate-900">
          {{ trim(($member['last_name'] ?? '') . ' ' . ($member['first_name'] ?? '')) ?: 'Chưa nhập tên' }}
        </p>
        <p class="text-xs text-slate-500 mt-0.5">
          {{ $familyRoles[$member['family_role'] ?? ''] ?? 'Chưa chọn vai trò' }}
          @if($this->saintName($member['saint_id'] ?? null))
          · {{ $this->saintName($member['saint_id']) }}
          @endif
          @if(($member['ref'] ?? '') === $submitter_ref)
          <span class="text-primary-600 font-medium">· Người đăng ký</span>
          @endif
          @php $sacCount = $this->memberSacramentCount($member['ref'] ?? ''); @endphp
          @if($sacCount > 0)
          <span>· {{ $sacCount }} bí tích</span>
          @endif
        </p>
      </div>
      <div class="flex gap-1 shrink-0">
        <button type="button" wire:click="openMemberForm({{ $index }})"
          class="px-2 py-1 text-xs rounded-lg border border-slate-300 text-slate-600">Sửa</button>
        @if(count($members) > 1)
        <button type="button" wire:click="removeMember({{ $index }})"
          class="px-2 py-1 text-xs rounded-lg border border-red-200 text-red-600">Xóa</button>
        @endif
      </div>
    </div>
  </div>
  @endforeach

  @error('members') <p class="text-red-500 text-xs">{{ $message }}</p> @enderror

  <button type="button" wire:click="openMemberForm"
    class="w-full py-2.5 rounded-xl border border-dashed border-primary-300 text-primary-700 text-sm font-semibold hover:bg-primary-50">
    + Thêm thành viên
  </button>

  @if($showMemberForm)
  <div class="rounded-xl border border-primary-200 bg-primary-50/40 p-4 space-y-3">
    <h3 class="text-sm font-bold text-slate-800">{{ $editingMemberIndex !== null ? 'Sửa thành viên' : 'Thêm thành viên' }}</h3>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Vai trò trong hộ</label>
      <select wire:model.defer="member_family_role" class="{{ $input }}">
        <option value="">-- Chọn --</option>
        @foreach($familyRoles as $value => $label)
        <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Họ và tên đệm <span class="text-red-500">*</span></label>
      <input wire:model.defer="member_last_name" type="text" class="{{ $input }}" />
      @error('member_last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Tên <span class="text-red-500">*</span></label>
      <input wire:model.defer="member_first_name" type="text" class="{{ $input }}" />
      @error('member_first_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Tên thánh</label>
      <x-searchable-select
        wireModel="member_saint_id"
        :options="$saints"
        placeholder="-- Chọn tên thánh --"
        labelKey="name"
        valueKey="id"
        :value="$member_saint_id" />
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Giới tính</label>
        <select wire:model.defer="member_gender" class="{{ $input }}">
          <option value="male">Nam</option>
          <option value="female">Nữ</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Con thứ</label>
        <input wire:model.defer="member_birth_order" type="number" min="1" class="{{ $input }}" />
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Ngày sinh</label>
      <input wire:model.defer="member_birthday" type="date" class="{{ $input }}" />
    </div>
    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Nơi sinh</label>
      <input wire:model.defer="member_birth_place" type="text" class="{{ $input }}" />
    </div>

    @if(count($members) > 0)
    <div class="grid grid-cols-1 gap-3 pt-1 border-t border-primary-100">
      <p class="text-xs font-semibold text-slate-500 uppercase">Cha / Mẹ (chọn từ thành viên đã thêm hoặc nhập tên)</p>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Cha (trong hộ)</label>
        <select wire:model.defer="member_father_ref" class="{{ $input }}">
          <option value="">-- Không chọn / nhập tên bên dưới --</option>
          @foreach($members as $m)
            @if(($m['ref'] ?? '') !== $member_ref)
            <option value="{{ $m['ref'] }}">{{ trim(($m['last_name'] ?? '') . ' ' . ($m['first_name'] ?? '')) }}</option>
            @endif
          @endforeach
        </select>
        <input wire:model.defer="member_father_name" type="text" class="{{ $input }} mt-2" placeholder="Hoặc tên cha (văn bản)" />
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">Mẹ (trong hộ)</label>
        <select wire:model.defer="member_mother_ref" class="{{ $input }}">
          <option value="">-- Không chọn / nhập tên bên dưới --</option>
          @foreach($members as $m)
            @if(($m['ref'] ?? '') !== $member_ref)
            <option value="{{ $m['ref'] }}">{{ trim(($m['last_name'] ?? '') . ' ' . ($m['first_name'] ?? '')) }}</option>
            @endif
          @endforeach
        </select>
        <input wire:model.defer="member_mother_name" type="text" class="{{ $input }} mt-2" placeholder="Hoặc tên mẹ (văn bản)" />
      </div>
    </div>
    @endif

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">Hội đoàn</label>
      @if(! $targetParishId)
      <p class="text-xs text-amber-700 mb-2">Chọn giáo xứ ở bước <strong>Hộ GĐ</strong> để hiển thị danh sách hội đoàn.</p>
      @elseif(empty($associationOptions))
      <p class="text-xs text-slate-500 mb-2">Giáo xứ chưa có hội đoàn trong hệ thống (có thể bỏ qua).</p>
      @endif
      <x-searchable-select
        wire:key="member-association-{{ $targetParishId ?? 'none' }}-{{ count($associationOptions) }}"
        wireModel="member_association_id"
        :options="$associationOptions"
        placeholder="{{ $targetParishId ? '-- Chọn hội đoàn --' : 'Chọn giáo xứ trước' }}"
        labelKey="name"
        valueKey="id"
        :value="$member_association_id" />
    </div>

    <div>
      <label class="block text-sm font-medium text-slate-700 mb-1">CCCD</label>
      <input wire:model.defer="member_cccd" type="text" class="{{ $input }}" />
    </div>

    @include('livewire.parishioners.partials.family-register-inline-sacraments')

    <div class="flex gap-2">
      <button type="button" wire:click="saveMember" class="px-4 py-2 rounded-xl bg-primary-600 text-white text-sm font-semibold">Lưu</button>
      <button type="button" wire:click="closeMemberForm" class="px-4 py-2 rounded-xl border border-slate-300 text-sm">Hủy</button>
    </div>
  </div>
  @endif
</div>
