<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceFile extends Model
{
    public $table = 'resource_files';

    protected $fillable = [
        'uuid',
        'mime_type',
        'size',
        'type',
        'url',
        'file_name'
    ];

    protected $hidden = [
        'url',
    ];

    public function resource_url()
    {
        return config('app.url') . '/c/files/' . $this->uuid;
    }
}
