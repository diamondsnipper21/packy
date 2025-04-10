<?php

namespace App\Mail;

use App\Models\CommunityMember;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserInviteMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $referUserName;
    public ?string $referUserPhoto;
    public string $communityName;
    public string $inviteLink;
    public string $language;

    /**
     * Create a new message instance.
     *
     * @param CommunityMember $member
     * @param string $communityName
     * @param string $inviteLink
     * @param string $language
     */
    public function __construct(
        CommunityMember $member,
        string $communityName,
        string $inviteLink,
        string $language
    ) {
        $this->referUserName = $member->user->name;
        $this->referUserPhoto = $member->user->photo;
        $this->communityName = $communityName;
        $this->inviteLink = $inviteLink;
        $this->language = $language;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $title = __('mail.invite.subject', [
            'name' => $this->referUserName,
            'community' => $this->communityName,
        ], $this->language);

        return $this->from(
            address: config('mail.support'),
            name: ucfirst(config('app.name'))
        )
            ->subject($title)
            ->view("emails.$this->language.invite-user-link")
            ->with([
                'title' => $title,
                'referUserName' => $this->referUserName,
                'referUserPhoto' => $this->referUserPhoto,
                'communityName' => $this->communityName,
                'inviteLink' => $this->inviteLink,
            ]);
    }
}
