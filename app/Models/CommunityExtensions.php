<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CommunityExtensions extends Model
{
    public $table = 'community_extensions';

    protected $fillable = [
        'community_id',
        'extension_id',
        'active'
    ];

    // active status
    public const TURN_ON = 1;
    public const TURN_OFF = 0;

    public function template(): HasOne
    {
        return $this->hasOne(AutoDmTemplates::class, 'extension_id', 'id');
    }

    public function extension(): HasOne
    {
        return $this->hasOne(\App\Models\Extensions::class, 'id', 'extension_id');
    }
}