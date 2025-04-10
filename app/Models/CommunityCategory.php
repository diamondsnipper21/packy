<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunityCategory extends Model
{
    public $table = 'community_categories';

    protected $fillable = [
        'community_id',
        'title',
        'description',
        'admin_only'
    ];
}