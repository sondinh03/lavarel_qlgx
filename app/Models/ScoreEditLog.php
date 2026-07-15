<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScoreEditLog extends Model
{
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';

    protected $table = 'score_edit_logs';

    protected $fillable = [
        'parish_id',
        'student_class_id',
        'score_type_id',
        'student_score_id',
        'old_value',
        'new_value',
        'action',
        'user_id',
    ];

    protected $casts = [
        'parish_id'        => 'integer',
        'student_class_id' => 'integer',
        'score_type_id'    => 'integer',
        'student_score_id' => 'integer',
        'old_value'        => 'decimal:2',
        'new_value'        => 'decimal:2',
        'user_id'          => 'integer',
    ];

    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scoreType(): BelongsTo
    {
        return $this->belongsTo(ScoreType::class);
    }

    public function studentClass(): BelongsTo
    {
        return $this->belongsTo(StudentsClass::class, 'student_class_id');
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            self::ACTION_CREATED => 'Thêm',
            self::ACTION_UPDATED => 'Sửa',
            self::ACTION_DELETED => 'Xóa',
            default              => $this->action,
        };
    }
}
