<?php

namespace App\Models;

use App\Support\Traits\BelongsToMember;
use Illuminate\Database\Eloquent\Model;

class AutoDmTemplates extends Model
{
    use BelongsToMember;

    public $table = 'auto_dm_templates';

    protected $fillable = [
        'community_id',
        'extension_id',
        'member_id',
        'body'
    ];
}