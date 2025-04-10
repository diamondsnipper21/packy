<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public $table = "users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'country',
        'password',
        'language',
        'token',
        'stripe_customer_id',
        'tag',
        'photo',
        'link',
        'bio',
        'timezone',
        'last_activity',
        'stripe_customer_id_marketplace'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'token',
        'remember_token',
        'stripe_customer_id',
        'stripe_customer_id_marketplace'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'lastChat',
        'name',
        'online',
        'countActivePlans'
    ];

    public function communities(): HasMany
    {
        return $this->hasMany(Community::class, 'user_id', 'id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(CommunityMember::class, 'id', 'user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class, 'user_id', 'id');
    }

    public function stripeAccount(): HasOne
    {
        return $this->hasOne(StripeAccount::class, 'user_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(UserPlansTransactions::class, 'user_id', 'id');
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(UserPaymentMethod::class, 'user_id');
    }

    public function paymentMethodsMarketplace(): HasMany
    {
        return $this->hasMany(PaymentMethodMarketplace::class, 'user_id');
    }

    /**
     * Get latest chat
     *
     * @return object|null
     */
    public function getLastChatAttribute(): ?object
    {
        $chat = null;

        $user = auth()->user();
        if (!empty($user)) {
            $chat = Chat::where([
                'from_id' => $this->id,
                'to_id' => $user->id
            ])
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->first();

            if (empty($chat)) {
                $chat = Chat::where([
                    'from_id' => $this->id,
                    'to_id' => $user->id
                ])
                ->orderBy('created_at', 'desc')
                ->first();
            }

            if (empty($chat)) {
                $chat = Chat::where([
                    'from_id' => $user->id,
                    'to_id' => $this->id
                ])
                ->orderBy('created_at', 'desc')
                ->first();
            }
        }

        return $chat;
    }

    /**
     * @return string
     */
    public function getNameAttribute(): string
    {
        $firstname = ucfirst(strtolower($this->firstname));
        $lastname = ucfirst(strtolower($this->lastname));

        return trim($firstname . ' ' . $lastname);
    }

    /**
     * Get start community of chatting
     *
     * @return object|null
     */
    public function getStartChatAttribute(): ?object
    {
        $chat = null;

        $user = auth()->user();
        if (!empty($user)) {
            $fromId = $user->id;
            $toId = $this->id;

            $chat = Chat::where(function ($query) use ($fromId, $toId) {
                $query->where('from_id', $fromId)
                ->where('to_id', $toId);
            })->orWhere(function ($query) use ($fromId, $toId) {
                $query->where('from_id', $toId)
                ->where('to_id', $fromId);
            })
            ->orderBy('created_at', 'asc')
            ->first();
        }

        return $chat;
    }

    /**
     * Get online property
     *
     * @return bool
     */
    public function getOnlineAttribute(): bool
    {
        $online = false;
        if (auth()->check() === true) {
            $date = new \DateTime();
            $date->modify('-15 minutes');
            $intervalTime = $date->format('Y-m-d H:i:s');
            if ($this->last_activity > $intervalTime) {
                $online = true;
            }
        }

        return $online;
    }

    /**
     * Returns total of active/trialing plans for the user
     *
     * @return int
     */
    public function getCountActivePlansAttribute(): int
    {
        $count = 0;

        foreach ($this->communities as $community) {
            foreach ($community->plans as $plan) {
                if ($plan->status === CommunityPlan::STATUS_ACTIVE ||
                    $plan->status === CommunityPlan::STATUS_TRIALING) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Get user info
     *
     * @return object|null
     */
    public static function getUserInfo(): ?object
    {
        return self::where(['id' => session('user_id')])
            ->with('transactions')
            ->with('paymentMethods')
            ->with('paymentMethodsMarketplace')
            ->first();
    }

    /**
     * @todo - move to UserService
     *
     * @return string
     */
    public static function generateUniqToken(): string
    {
        $valid = false;
        $token = '';

        while ($valid == false) {
            $token = \Str::random(32);
            $count = self::where('token', '=', $token)->count();
            if ($count == 0) {
                $valid = true;
                break;
            }
        }

        return $token;
    }
}
