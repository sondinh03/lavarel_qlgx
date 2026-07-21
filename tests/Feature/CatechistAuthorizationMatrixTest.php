<?php

namespace Tests\Feature;

use App\Models\StudentScore;
use App\Services\CatechistAccess;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Tests\Support\CatechistAuthFixture;
use Tests\TestCase;

class CatechistAuthorizationMatrixTest extends TestCase
{
    use DatabaseTransactions;

    private CatechistAuthFixture $fx;

    private CatechistAccess $access;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fx = CatechistAuthFixture::make();
        $this->access = app(CatechistAccess::class);
    }

    public function test_ordinary_catechist_views_only_assigned_student_and_class_scores(): void
    {
        $user = $this->fx->ordinaryCatechist;

        $this->assertTrue($user->can('view', $this->fx->studentAssigned));
        $this->assertFalse($user->can('view', $this->fx->studentOtherSameParish));
        $this->assertFalse($user->can('view', $this->fx->studentOtherParish));

        $this->assertFalse($user->can('update', $this->fx->studentAssigned));
        $this->assertFalse($user->can('create', \App\Models\StudentNew::class));
        $this->assertFalse($user->can('delete', $this->fx->studentAssigned));
        $this->assertFalse($user->can('linkParishioner', $this->fx->studentAssigned));

        $this->assertTrue($user->can('viewScoresForClass', $this->fx->classAssigned));
        $this->assertFalse($user->can('viewScoresForClass', $this->fx->classOtherSameParish));
        $this->assertFalse($user->can('viewScoresForClass', $this->fx->classOtherParish));

        $this->assertFalse($user->can('enterScoresForClass', $this->fx->classAssigned));
        $this->assertFalse($user->can('enterScores', StudentScore::class));
        $this->assertFalse($this->access->canManageParishScores($user));
    }

    public function test_score_manager_catechist_can_enter_all_parish_classes_when_window_open(): void
    {
        $user = $this->fx->scoreManagerCatechist;

        $this->assertTrue($user->can('viewScoresForClass', $this->fx->classAssigned));
        $this->assertTrue($user->can('viewScoresForClass', $this->fx->classOtherSameParish));
        $this->assertFalse($user->can('viewScoresForClass', $this->fx->classOtherParish));

        $this->assertTrue($user->can('enterScoresForClass', $this->fx->classAssigned));
        $this->assertTrue($user->can('enterScoresForClass', $this->fx->classOtherSameParish));
        $this->assertFalse($user->can('enterScoresForClass', $this->fx->classOtherParish));
        $this->assertTrue($user->can('enterScores', StudentScore::class));

        $this->fx->parishA->update(['scores_entry_open' => false]);
        $user = $user->fresh();

        $this->assertFalse($user->can('enterScoresForClass', $this->fx->classAssigned));
        $this->assertFalse($user->can('enterScores', StudentScore::class));
    }

    public function test_student_editor_catechist_can_update_parish_students_but_not_create_delete_or_cross_parish(): void
    {
        $user = $this->fx->studentEditorCatechist;

        $this->assertTrue($user->can('view', $this->fx->studentAssigned));
        $this->assertTrue($user->can('view', $this->fx->studentOtherSameParish));
        $this->assertFalse($user->can('view', $this->fx->studentOtherParish));

        $this->assertTrue($user->can('update', $this->fx->studentAssigned));
        $this->assertTrue($user->can('update', $this->fx->studentOtherSameParish));
        $this->assertFalse($user->can('update', $this->fx->studentOtherParish));

        $this->assertFalse($user->can('create', \App\Models\StudentNew::class));
        $this->assertFalse($user->can('delete', $this->fx->studentAssigned));
        $this->assertFalse($user->can('linkParishioner', $this->fx->studentAssigned));

        $this->assertFalse($user->can('enterScoresForClass', $this->fx->classAssigned));
    }

    public function test_catechism_admin_manages_students_and_scores_in_parish_but_cannot_grant_elevated(): void
    {
        $user = $this->fx->catechismAdmin;

        $this->assertTrue($user->can('view', $this->fx->studentAssigned));
        $this->assertTrue($user->can('update', $this->fx->studentAssigned));
        $this->assertTrue($user->can('create', \App\Models\StudentNew::class));
        $this->assertTrue($user->can('delete', $this->fx->studentAssigned));
        $this->assertTrue($user->can('enterScoresForClass', $this->fx->classAssigned));
        $this->assertFalse($user->can('enterScoresForClass', $this->fx->classOtherParish));

        $this->assertFalse($this->access->canGrantElevatedPermissions($user));
    }

    public function test_parish_admin_can_grant_elevated_permissions(): void
    {
        $this->assertTrue($this->access->canGrantElevatedPermissions($this->fx->parishAdmin));
        $this->assertFalse($this->access->canGrantElevatedPermissions($this->fx->ordinaryCatechist));
        $this->assertFalse($this->access->canGrantElevatedPermissions($this->fx->catechismAdmin));
    }

    public function test_assigned_class_ids_come_from_class_teachers_user_id(): void
    {
        $ids = $this->access->assignedClassIds($this->fx->ordinaryCatechist, $this->fx->parishA->id);

        $this->assertSame([(int) $this->fx->classAssigned->id], $ids);
        $this->assertSame(
            [],
            $this->access->assignedClassIds($this->fx->scoreManagerCatechist, $this->fx->parishA->id)
        );
    }

    public function test_authorize_enter_scores_for_class_denies_ordinary_catechist(): void
    {
        $this->actingAs($this->fx->ordinaryCatechist);

        $this->assertFalse(Gate::allows('enterScoresForClass', $this->fx->classAssigned));
        $this->assertTrue(Gate::allows('viewScoresForClass', $this->fx->classAssigned));
    }
}
