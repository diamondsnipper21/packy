<?php

namespace App\Services;

use App\Models\CommunityMember;
use App\Models\StripePrices;
use App\Models\Signups;
use App\Models\Visit;
use App\Models\Members\Subscriptions\MemberSubscriptions;
use Carbon\Carbon;

class CommunityStatsService extends Service
{
    const CHURN_LAST_DAY = 30;
    const TRAFFIC_LAST_DAY = 7;

    /**
    * Get admin stats of community
    *
    * @param int $communityId
    * @return array
    */
    public function getStatsByCommunity(int $communityId): array
    {
        return [
            'subscriptions' => [
                'paid_members' => $this->getSubscriptionsPaidMembers($communityId),
                'mrr' => $this->getSubscriptionsMRR($communityId),
                'churn' => $this->getSubscriptionsChurn($communityId)
            ],
            'traffic' => [
                'about_page_visitors' => $this->getTrafficAboutPageVisitors($communityId),
                'signups' => $this->getTrafficSignups($communityId)
            ]
        ];
    }

    /**
    * Get count of paid members
    *
    * @param int $communityId
    * @return int
    */
    private function getSubscriptionsPaidMembers(int $communityId): int
    {
        $memberIds = CommunityMember::leftJoin('community_member_subscriptions', function ($join) use($communityId) {
                $join->on('community_members.id', '=', 'community_member_subscriptions.member_id');
                $join->where('community_member_subscriptions.community_id','=', $communityId);
                $join->where('community_member_subscriptions.status','!=', MemberSubscriptions::STATUS_CANCELLED);
            })
            ->where('community_members.community_id', $communityId)
            ->where('community_member_subscriptions.status', MemberSubscriptions::STATUS_ACTIVE)
            ->pluck('community_members.id')
            ->toArray();

        return count(array_unique($memberIds));
    }


    /**
    * Get monthly recurring revenue 
    *
    * @param int $communityId
    * @return int
    */
    private function getSubscriptionsMRR(int $communityId): int
    {
        $mrr = 0;
        $startDate = Carbon::now()->format('Y-m-d');
        $endDate = Carbon::now()->addMonth()->format('Y-m-d');

        $priceIds = MemberSubscriptions::where('next_billing_at', '>=', $startDate . ' 00:00:00')
            ->where('next_billing_at', '<=', $endDate . ' 23:59:59')
            ->where('period', '=', MemberSubscriptions::PERIOD_MONTHLY)
            ->where('status', '=', MemberSubscriptions::STATUS_ACTIVE)
            ->where('community_id', $communityId)
            ->pluck('price_id')
            ->toArray();

        if (!empty($priceIds)) {
            foreach ($priceIds as $priceId) {
                $price = StripePrices::find($priceId);
                if (!empty($price) && !empty($price->amount_monthly)) {
                    $mrr += $price->amount_monthly;
                }
            }
        }

        return $mrr;
    }

    /**
    * Get churn
    *
    * @param int $communityId
    * @return float
    */
    private function getSubscriptionsChurn(int $communityId): float
    {
        $startDate = Carbon::now()->subDays(self::CHURN_LAST_DAY)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $totalUserIds = MemberSubscriptions::where('community_id', $communityId)
            ->pluck('member_id')
            ->toArray();

        $totalCount = count(array_unique($totalUserIds));

        $lostUserIds = MemberSubscriptions::where('cancelled_at', '>=', $startDate . ' 00:00:00')
            ->where('cancelled_at', '<=', $endDate . ' 23:59:59')
            ->where('community_id', $communityId)
            ->pluck('member_id')
            ->toArray();

        $lostCount = count(array_unique($lostUserIds));

        $churn = 0;
        if ($totalCount > 0) {
            $churn = $lostCount * 100 / (float)$totalCount;
        }

        return $churn;
    }

    /**
    * Get count of about page visitors
    *
    * @param int $communityId
    * @return int
    */
    private function getTrafficAboutPageVisitors(int $communityId): int
    {
        $startDate = Carbon::now()->subDays(self::TRAFFIC_LAST_DAY)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $ipAddresses = Visit::where('created_at', '>=', $startDate . ' 00:00:00')
            ->where('created_at', '<=', $endDate . ' 23:59:59')
            ->where('community_id', $communityId)
            ->pluck('ip_address')
            ->toArray();

        return count(array_unique($ipAddresses));
    }

    /**
    * Get count of sign ups from about page
    *
    * @param int $communityId
    * @return int
    */
    private function getTrafficSignups(int $communityId): int
    {
        $startDate = Carbon::now()->subDays(self::TRAFFIC_LAST_DAY)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $memberIds = CommunityMember::getMemberIds($communityId);
        $allowedMemberIds = $memberIds['allowed'];

        $userIds = CommunityMember::whereIn('id', $allowedMemberIds)
            ->pluck('user_id')
            ->toArray();

        $signupIps = Signups::where('community_id', $communityId)
            ->whereIn('user_id', $userIds)
            ->whereIn('origin', Visit::TRACKED_PAGES)
            ->where('date', '>=', $startDate . ' 00:00:00')
            ->where('date', '<=', $endDate . ' 23:59:59')
            ->pluck('ip')
            ->toArray();

        return count(array_unique($signupIps));
    }
}
