<?php

namespace Tests\Feature;

use App\Http\Livewire\AttendanceManager;
use App\Http\Livewire\Score\ScoreManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class CatechistLivewireScopeTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
    }

    public function test_ordinary_catechist_can_view_but_not_edit_assigned_class_scores(): void
    {
        $pivotId = $this->fx->pivotAssigned->id;
        $typeId = $this->fx->scoreTypeAssigned->id;

        Livewire::actingAs($this->fx->ordinaryCatechist)
            ->test(ScoreManager::class)
            ->set('selectedLop', $this->fx->classAssigned->id)
            ->assertSet('canViewScores', true)
            ->assertSet('canEditScores', false)
            ->set("draftScores.{$pivotId}.{$typeId}", 8)
            ->call('saveAllScores')
            ->assertEmitted('toast', 'error', 'Hiện chưa mở cửa sổ nhập/sửa điểm hoặc bạn không có quyền');

        $this->assertDatabaseMissing('student_scores', [
            'student_class_id' => $pivotId,
            'score_type_id' => $typeId,
        ]);
    }

    public function test_score_manager_rejects_forged_student_class_payload(): void
    {
        $forgedPivot = $this->fx->pivotOtherSameParish->id;
        $typeId = $this->fx->scoreTypeAssigned->id;

        Livewire::actingAs($this->fx->scoreManagerCatechist)
            ->test(ScoreManager::class)
            ->set('selectedLop', $this->fx->classAssigned->id)
            ->assertSet('canEditScores', true)
            ->set("draftScores.{$forgedPivot}.{$typeId}", 9)
            ->call('saveAllScores')
            ->assertEmitted('toast', 'error', 'Phát hiện dữ liệu điểm không thuộc lớp đang chọn');

        $this->assertDatabaseMissing('student_scores', [
            'student_class_id' => $forgedPivot,
        ]);
    }

    public function test_score_manager_can_save_for_elevated_catechist(): void
    {
        $pivotId = $this->fx->pivotOtherSameParish->id;
        $typeId = $this->fx->scoreTypeOther->id;

        Livewire::actingAs($this->fx->scoreManagerCatechist)
            ->test(ScoreManager::class)
            ->set('selectedLop', $this->fx->classOtherSameParish->id)
            ->assertSet('canEditScores', true)
            ->set("draftScores.{$pivotId}.{$typeId}", 7.5)
            ->call('saveAllScores')
            ->assertEmitted('toast', 'message', 'Đã lưu 1 điểm');

        $this->assertDatabaseHas('student_scores', [
            'student_class_id' => $pivotId,
            'score_type_id' => $typeId,
        ]);
    }

    public function test_attendance_rejects_class_from_other_parish(): void
    {
        Livewire::actingAs($this->fx->ordinaryCatechist)
            ->test(AttendanceManager::class)
            ->set('selectedClassId', $this->fx->classOtherParish->id)
            ->assertSet('selectedClassId', null);
    }

    public function test_attendance_allows_any_class_in_same_parish_for_ordinary_catechist(): void
    {
        Livewire::actingAs($this->fx->ordinaryCatechist)
            ->test(AttendanceManager::class)
            ->set('selectedClassId', $this->fx->classOtherSameParish->id)
            ->assertSet('selectedClassId', $this->fx->classOtherSameParish->id)
            ->assertHasNoErrors();
    }
}
