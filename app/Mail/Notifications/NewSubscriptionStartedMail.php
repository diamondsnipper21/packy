<?php

namespace App\Mail\Notifications;

use App\Enum\LangEnum;
use App\Models\Community;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewSubscriptionStartedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    private string $adminName;
    private string $adminEmail;
    private string $memberName;
    private string $price;
    private string $communityName;
    private string $communityUrl;
    private string $trial;
    private string $language;

    /**
     * @param Community $community
     * @param User $user
     * @param string $price,
     */
    public function __construct(
        Community $community,
        User $user,
        string $price
    ) {
        $communityOwner = $community->user;
        $this->adminName = $communityOwner->name;

        $replyTo = $communityOwner->email;
        if ($communityOwner->id === 1) {
            $replyTo = config('mail.support');
        }

        $this->adminEmail = $replyTo;
        $this->communityName = $community->name;
        $this->communityUrl = $community->url;
        $this->trial = (bool)$community->trial_days;
        $this->memberName = $user->name;
        $this->price = $price;
        $this->language = $user->language ?? LangEnum::LANG_ENGLISH;
    }

    /**
     * @return self
     */
    public function build(): self
    {
        $title = $this->trial ?
        __('mail.new-subscription-started.title-new-trial', [], $this->language) :
        __('mail.new-subscription-started.title-new-subscription', [], $this->language);

        return $this->from(
            address: config('mail.support'),
            name: ucfirst(config('app.name'))
        )
        ->replyTo($this->adminEmail)
        ->subject($title)
        ->view("emails.$this->language.new-subscription-started")
        ->with([
            'title' => $title,
            'adminName' => $this->adminName,
            'memberName' => $this->memberName,
            'price' => $this->price,
            'communityName' => $this->communityName,
            'communityUrl' => $this->communityUrl,
            'trial' => $this->trial
        ]);
    }
}
