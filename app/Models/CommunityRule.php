<?php

namespace App\Models;

use App\Support\Traits\BelongsToCommunity;
use Illuminate\Database\Eloquent\Model;

class CommunityRule extends Model
{
    use BelongsToCommunity;

    public $table = 'community_rules';

    protected $fillable = [
        'community_id',
        'title',
        'description',
        'order'
    ];

    /**
     * Generate new rule order
     * 
     * @param int $communityId
     * @return int
     */
    public static function generateNewRuleOrder(int $communityId): int
    {
        $order = 1;

        $lastRule = self::where(['community_id' => $communityId])->orderBy('order', 'desc')->first();
        if (!empty($lastRule)) {
            $order = $lastRule->order + 1;
        }

        return $order;
    }

    /**
     * Re-arrange rules' order
     * 
     * @param int $communityId
     * @return array
     */
    public static function reArrangeRuleOrder(int $communityId, int $order): array
    {
        $rules = self::where([['community_id', '=', $communityId], ['order', '>', $order]])->get();
        if (!empty($rules)) {
            foreach ($rules as $key => $rule) {
                try {
                    $rule->order = $rule->order - 1;
                    $rule->save();
                } catch (\Exception $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }
            }
        }

        return ['success' => true];
    }
}