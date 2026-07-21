<?php

namespace Tests\Feature;

use App\Http\Livewire\Landing;
use App\Models\StudentNew;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class LandingPublicLookupSecurityTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
    }

    public function test_forged_viewing_student_id_does_not_leak_scores_or_attendance(): void
    {
        $student = $this->fx->studentAssigned;
        $other = $this->fx->studentOtherSameParish;

        $component = Livewire::test(Landing::class)
            ->set('phone', $student->phone)
            ->call('search')
            ->assertSet('viewingStudentId', $student->id);

        $component
            ->set('viewingStudentId', $other->id)
            ->call('switchTab', 'scores')
            ->assertSet('viewingStudentId', null);

        $component = Livewire::test(Landing::class)
            ->set('phone', $student->phone)
            ->call('search')
            ->set('viewingStudentId', $other->id);

        $this->assertSame([], $component->instance()->scoresSummary);
        $this->assertSame([], $component->instance()->attendanceSummary);
        $this->assertNull($component->get('viewingStudentId'));
    }

    public function test_view_student_rejects_id_not_in_search_results(): void
    {
        $student = $this->fx->studentAssigned;

        Livewire::test(Landing::class)
            ->set('phone', $student->phone)
            ->call('search')
            ->call('viewStudent', $this->fx->studentOtherParish->id)
            ->assertSet('viewingStudentId', null)
            ->assertSet('error', 'Không tìm thấy học viên nào với số điện thoại này.');
    }

    public function test_mismatched_phone_clears_forged_viewing_state(): void
    {
        $student = $this->fx->studentAssigned;

        Livewire::test(Landing::class)
            ->set('phone', $student->phone)
            ->call('search')
            ->set('phone', '0999888777')
            ->set('viewingStudentId', $student->id)
            ->call('switchTab', 'attendance')
            ->assertSet('viewingStudentId', null);
    }
}
