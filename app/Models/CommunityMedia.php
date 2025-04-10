<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityMedia extends Model
{
    public $table = 'community_media';

    protected $fillable = [
        'owner',
        'owner_id',
        'type',
        'path',
        'ext',
        'filename',
        'order'
    ];

    public const OWNER_COMMUNITY = 'community';
    public const OWNER_POST = 'post';
    public const OWNER_SCHEDULED_POST = 'scheduled_post';
    public const OWNER_COMMENT = 'comment';
    public const OWNER_CHAT = 'chat';

    public const TYPE_IMAGE = 'image';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_VIDEO = 'video';
    public const TYPE_PDF = 'pdf';
    public const TYPE_FILE = 'file';
}
