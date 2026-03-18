<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AttendanceSession;
use App\Models\StudentNew;
use App\Http\Livewire\AttendanceManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

class AttendancePageTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::find(13);
    }

    /** Trang có load được không */
    public function test_trang_diem_danh_mo_duoc(): void
    {
        $this->actingAs($this->user)
            ->get(route('attendance.show'))
            ->assertStatus(200);
    }

    /** Chưa chọn lớp thì không bị lỗi */
    public function test_chua_chon_lop_khong_bi_loi(): void
    {
        Livewire::actingAs($this->user)
            ->test(AttendanceManager::class)
            ->assertHasNoErrors();
    }

    /** Chọn lớp thì load được học sinh */
    public function test_chon_lop_load_duoc_hoc_sinh(): void
    {
        $session = AttendanceSession::factory()->open()->create();

        Livewire::actingAs($this->user)
            ->test(AttendanceManager::class)
            ->set('selectedClassId', $session->class_id)
            ->assertHasNoErrors();
    }

    /** Lưu điểm danh từ component thành công */
    public function test_luu_diem_danh_tu_component(): void
    {
        $session = AttendanceSession::factory()->open()->create();
        $student = StudentNew::first();

        Livewire::actingAs($this->user)
            ->test(AttendanceManager::class, [
                'selectedClassId' => $session->class_id,
            ])
            ->call('saveFromClient', [
                "{$student->id}_{$session->id}" => [
                    'status' => 1,
                    'note'   => '',
                ],
            ])
            ->assertDispatchedBrowserEvent('attendance-saved')
            ->assertHasNoErrors();
    }

    /** Không đăng nhập thì không vào được */
    public function test_chua_dang_nhap_bi_redirect(): void
    {
        $this->get(route('attendance.show'))
            ->assertRedirect(route('login'));
    }
}
