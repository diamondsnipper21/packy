<?php

namespace App\Models;

use App\Support\Traits\BelongsToUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InviteUserTokens extends Model
{
    use BelongsToUser;

    public $table = 'invite_user_tokens';

    protected $fillable = [
        'user_id',
        'community_id',
        'token',
        'auto_approval'
    ];

    /**
     * @return string
     */
    public static function generateUniqToken(): string
    {
        $valid = false;
        $token = '';

        while ($valid == false) {
            $token = \Str::random(7);
            $count = self::where('token', '=', $token)->count();
            if ($count == 0) {
                $valid = true;
                break;
            }
        }

        return $token;
    }
}
