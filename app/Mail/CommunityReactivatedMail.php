<?php

namespace App\Mail;

use App\Models\Community;

class CommunityReactivatedMail extends SupportMail
{
    /**
     * @param Community $community
     */
    public function __construct(Community $community)
    {
        parent::__construct($community->user);

        $this->id = 'community-reactivated';
        $this->with = [
            'communityName' => $community->name,
            'communityLogo' => $community->logo,
            'communityUrl' => config('app.url') . '/' . $community->url,
        ];
    }
}
