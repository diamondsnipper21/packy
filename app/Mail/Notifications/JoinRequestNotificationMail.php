<?php

namespace App\Mail\Notifications;

use App\Models\Community;
use App\Models\CommunityMember;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JoinRequestNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $communityName;
    public string $communityLink;
    public string $communityOwnerEmail;
    public int $access;
    public string $feedback;
    public string $language;
    public ?string $communityLogo;
    public ?string $communitySummaryPhoto;
    public ?string $communitySummaryDescription;

    /**
     * Create a new message instance.
     *
     * @param Community $community
     * @param string $communityLink
     * @param int $access
     * @param string $feedback
     * @param string $language
     */
    public function __construct(
        Community $community,
        string $communityLink,
        int $access,
        string $feedback,
        string $language
    ) {
        $this->communityName = $community->name;
        $this->communityLogo = $community->logo;
        $this->communitySummaryPhoto = $community->summary_photo;
        $this->communitySummaryDescription = $community->summary_description;
        $this->communityLink = $communityLink;
        $this->access = $access;
        $this->feedback = $feedback;
        $this->language = $language;

        $communityOwner = $community->user;

        $replyTo = $communityOwner->email;
        if ($communityOwner->id === 1) {
            $replyTo = config('mail.support');
        }

        $this->communityOwnerEmail = $replyTo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->access === CommunityMember::ACCESS_ALLOWED) {
            $subject = 'mail.approve-request.subject';
            $view = 'send-notification-for-approve-request';
        } else if ($this->access === CommunityMember::ACCESS_DECLINE) {
            $subject = 'mail.decline-request.subject';
            $view = 'send-notification-for-decline-request';
        } else if ($this->access === CommunityMember::ACCESS_REMOVED) {
            $subject = 'mail.removed-request.subject';
            $view = 'send-notification-for-remove-group';
        }

        $title = __($subject, ['name' => $this->communityName], $this->language);

        return $this->from(
            address: config('mail.support'),
            name: ucfirst(config('app.name'))
        )
        ->replyTo($this->communityOwnerEmail)
        ->subject($title)
        ->view("emails.$this->language." . $view)
        ->with([
            'title' => $title,
            'communityName' => $this->communityName,
            'communityUrl' => $this->communityLink,
            'communityLogo' => $this->communityLogo,
            'communitySummaryPhoto' => $this->communitySummaryPhoto,
            'communitySummaryDescription' => $this->communitySummaryDescription,
            'feedback' => $this->feedback,
        ]);
    }
}
