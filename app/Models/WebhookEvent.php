<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    public $table = 'webhook_events';

    protected $fillable = [
        'source',
        'event_type',
        'body',
        'headers',
        'treated_at'
    ];
}
