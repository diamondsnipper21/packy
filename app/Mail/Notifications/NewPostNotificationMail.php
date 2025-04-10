<?php

namespace App\Mail\Notifications;

use App\Models\CommunityMember;
use App\Models\CommunityPost;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewPostNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $creatorName;
    public ?string $creatorPhoto;
    public string $postTitle;
    public string $postContent;
    public ?object $firstMedia;
    public string $link;
    public string $replyToEmailAddress;
    public string $language;

    /**
     * Create a new message instance.
     *
     * @param CommunityMember $member
     * @param CommunityPost $communityPost
     * @param string $link
     * @param string $replyToEmailAddress
     * @param string $language
     */
    public function __construct(
        CommunityMember $member,
        CommunityPost $communityPost,
        string $link,
        string $replyToEmailAddress,
        string $language
    ) {
        $this->creatorName = $member->user->name;
        $this->creatorPhoto = $member->user->photo;
        $this->postTitle = $communityPost->title;
        $this->postContent = $communityPost->content;
        $this->firstMedia = $communityPost->firstMedia;
        $this->link = $link;
        $this->replyToEmailAddress = $replyToEmailAddress;
        $this->language = $language;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $title = __('mail.post-notification.subject', [
            'name' => $this->creatorName
        ], $this->language);

        if (!empty($this->postTitle)) {
            $title = $this->postTitle;
        }

        return $this->from(
            address: config('mail.support'),
            name: ucfirst(config('app.name'))
        )
        ->replyTo($this->replyToEmailAddress)
        ->subject($title)
        ->view("emails.$this->language.send-notification-for-new-post")
        ->with([
            'creatorName' => $this->creatorName,
            'creatorPhoto' => $this->creatorPhoto,
            'postTitle' => $this->postTitle,
            'postContent' => $this->postContent,
            'firstMedia' => $this->firstMedia,
            'inviteLink' => $this->link,
        ]);
    }
}
