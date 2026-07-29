<?php

namespace Tests\Feature;

use App\Http\Livewire\Score\ScoreManager;
use App\Http\Livewire\Score\ScoreStatistics;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\GradingSetting;
use App\Models\ScoreType;
use App\Models\StudentScore;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class GradingSettingsTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
    }

    public function test_admin_saves_weights_for_selected_school_year(): void
    {
        Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->call('switchTab', 'weights')
            ->set('weightAcademic', 70)
            ->set('weightClassAttendance', 20)
            ->set('weightMassAttendance', 10)
            ->set('weightSemester1', 40)
            ->set('weightSemester2', 60)
            ->set('excusedCreditPercent', 25)
            ->call('saveGradingSettings')
            ->assertHasNoErrors()
            ->assertSet('weightOverrideExists', true);

        $this->assertDatabaseHas('grading_settings', [
            'parish_id'               => $this->fx->parishA->id,
            'school_year_id'          => $this->fx->yearA->id,
            'grade_level_id'          => null,
            'weight_academic'         => 70,
            'weight_class_attendance' => 20,
            'weight_mass_attendance'  => 10,
            'weight_semester_1'       => 40,
            'weight_semester_2'       => 60,
            'excused_credit_percent'  => 25,
        ]);
    }

    public function test_component_weights_must_sum_to_one_hundred(): void
    {
        Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('weightAcademic', 70)
            ->set('weightClassAttendance', 20)
            ->set('weightMassAttendance', 20)
            ->call('saveGradingSettings')
            ->assertHasErrors('weightAcademic');

        $this->assertDatabaseMissing('grading_settings', [
            'parish_id' => $this->fx->parishA->id,
        ]);
    }

    public function test_semester_weights_must_sum_to_one_hundred(): void
    {
        Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('weightSemester1', 60)
            ->set('weightSemester2', 60)
            ->call('saveGradingSettings')
            ->assertHasErrors('weightSemester1');

        $this->assertDatabaseMissing('grading_settings', [
            'parish_id' => $this->fx->parishA->id,
        ]);
    }

    public function test_grade_scope_requires_a_grade(): void
    {
        Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('selectedKhoi', null)
            ->call('switchTab', 'weights')
            ->set('weightScope', 'grade')
            ->set('weightScopeGradeId', null)
            ->assertSee('Chọn khối')
            ->call('saveGradingSettings')
            ->assertHasErrors('weightScopeGradeId');
    }

    public function test_deleting_override_returns_to_inherited_weights(): void
    {
        GradingSetting::query()->create(array_merge(GradingSetting::DEFAULTS, [
            'parish_id'               => $this->fx->parishA->id,
            'school_year_id'          => $this->fx->yearA->id,
            'weight_academic'         => 50,
            'weight_class_attendance' => 50,
            'weight_mass_attendance'  => 0,
        ]));

        Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->call('switchTab', 'weights')
            ->assertSet('weightOverrideExists', true)
            ->call('deleteGradingSettings')
            ->assertSet('weightOverrideExists', false)
            ->assertSet('weightAcademic', 100.0);

        $this->assertDatabaseMissing('grading_settings', [
            'parish_id'      => $this->fx->parishA->id,
            'school_year_id' => $this->fx->yearA->id,
        ]);
    }

    public function test_score_table_shows_attendance_columns_only_when_weighted(): void
    {
        Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('selectedLop', $this->fx->classAssigned->id)
            ->assertDontSee('Chuyên cần');

        GradingSetting::query()->create(array_merge(GradingSetting::DEFAULTS, [
            'parish_id'               => $this->fx->parishA->id,
            'school_year_id'          => $this->fx->yearA->id,
            'weight_academic'         => 60,
            'weight_class_attendance' => 25,
            'weight_mass_attendance'  => 15,
        ]));

        Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('selectedLop', $this->fx->classAssigned->id)
            ->assertSee('Chuyên cần');
    }

    public function test_score_table_hides_inactive_score_types(): void
    {
        ScoreType::query()->create([
            'class_id'     => $this->fx->classAssigned->id,
            'semester'     => ScoreType::SEMESTER_1,
            'type'         => ScoreType::TYPE_15_PHUT,
            'name'         => 'Điểm đã tắt',
            'order'        => 99,
            'coefficient'  => 1,
            'max_score'    => 10,
            'is_active'    => false,
        ]);

        $component = Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('selectedLop', $this->fx->classAssigned->id)
            ->set('selectedSemester', ScoreType::SEMESTER_1)
            ->assertSee((string) $this->fx->scoreTypeAssigned->name)
            ->assertDontSee('Điểm đã tắt');

        $component
            ->call('switchTab', 'config')
            ->assertSee('Điểm đã tắt');
    }

    public function test_weights_tab_shows_rating_scale(): void
    {
        Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->call('switchTab', 'weights')
            ->assertSee('Cách xếp loại học lực')
            ->assertSee('Xuất sắc')
            ->assertSee('Từ 9,5 đến 10')
            ->assertSee('Từ 6,5 đến dưới 8,0')
            ->assertSee('Dưới 3,5');
    }

    public function test_plain_catechist_cannot_open_weights_tab(): void
    {
        Livewire::actingAs($this->fx->ordinaryCatechist)
            ->test(ScoreManager::class)
            ->call('switchTab', 'weights')
            ->assertSet('activeTab', 'scores');
    }

    public function test_statistics_and_score_table_report_the_same_weighted_average(): void
    {
        StudentScore::query()->create([
            'student_class_id' => $this->fx->pivotAssigned->id,
            'score_type_id'    => $this->fx->scoreTypeAssigned->id,
            'score_value'      => 8,
            'attempt'          => 1,
        ]);

        $session = AttendanceSession::query()->create([
            'class_id' => $this->fx->classAssigned->id,
            'date'     => now()->subDay()->toDateString(),
            'semester' => ScoreType::SEMESTER_1,
            'type'     => AttendanceSession::TYPE_CLASS,
            'status'   => AttendanceSession::STATUS_CLOSED,
        ]);

        AttendanceRecord::query()->create([
            'session_id' => $session->id,
            'student_id' => $this->fx->studentAssigned->id,
            'status'     => AttendanceRecord::STATUS_PRESENT,
        ]);

        GradingSetting::query()->create(array_merge(GradingSetting::DEFAULTS, [
            'parish_id'               => $this->fx->parishA->id,
            'school_year_id'          => $this->fx->yearA->id,
            'weight_academic'         => 50,
            'weight_class_attendance' => 50,
            'weight_mass_attendance'  => 0,
        ]));

        // 8×0.5 + 10×0.5 = 9
        Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreManager::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('selectedLop', $this->fx->classAssigned->id)
            ->assertSet('averages.' . $this->fx->pivotAssigned->id, 9.0);

        Livewire::actingAs($this->fx->parishAdmin)
            ->test(ScoreStatistics::class)
            ->set('selectedNamHoc', $this->fx->yearA->id)
            ->set('selectedLop', $this->fx->classAssigned->id)
            ->set('selectedSemester', 1)
            ->assertSet('totalStudentsWithScore', 1)
            ->assertSet('summary.avg', 9.0);
    }
}
