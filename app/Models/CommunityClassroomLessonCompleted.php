<?php

namespace App\Models;

use App\Services\LoggerService;
use App\Support\Traits\BelongsToMember;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityClassroomLessonCompleted extends Model
{
    use BelongsToMember;

    public $table = 'community_classrooms_lessons_completed';

    protected $fillable = [
        'community_id',
        'member_id',
        'classroom_id',
        'lesson_id',
        'completed_at'
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CommunityClassroomLesson::class, 'lesson_id', 'id');
    }

    /**
     * @param array $arrayInsert
     * @return array
     */
    public static function createLessonCompleted(array $arrayInsert): array
    {
        extract($arrayInsert);

        try {
            $lessonCompleted = new CommunityClassroomLessonCompleted();
            $lessonCompleted->community_id = $community_id;
            $lessonCompleted->member_id = $member_id;
            $lessonCompleted->classroom_id = $classroom_id;
            $lessonCompleted->lesson_id = $lesson_id;
            $lessonCompleted->completed_at = date('Y-m-d H:i:s');
            $lessonCompleted->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false];
        }

        return ['success' => true];
    }
}
