<?php

namespace App\Mail;

use App\Models\Community;

class CommunityCreatedMail extends SupportMail
{
    /**
     * @param Community $community
     */
    public function __construct(Community $community)
    {
        parent::__construct($community->user);

        $this->id = 'community-created';
        $this->with = [
            'userName' => $community->user->name,
            'communityName' => $community->name,
            'communityUrl' => config('app.url') . '/' . $community->url,
        ];
    }
}
