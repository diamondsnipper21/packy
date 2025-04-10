<?php

namespace App\Models;

use App\Enum\FileTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonResources extends Model
{
    public $table = 'lesson_resources';

    protected $fillable = [
        'lesson_id',
        'type',
        'label',
        'url'
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CommunityClassroomLesson::class, 'lesson_id');
    }

    /**
     * Get possible type arrays
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return [
            FileTypeEnum::TYPE_LINK,
            FileTypeEnum::TYPE_VIDEO,
            FileTypeEnum::TYPE_AUDIO,
            FileTypeEnum::TYPE_IMAGE,
            FileTypeEnum::TYPE_PDF
        ];
    }
}
