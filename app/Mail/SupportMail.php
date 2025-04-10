<?php

namespace App\Mail;

use App\Enum\LangEnum;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SupportMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected string $id;
    protected string $lang;
    protected string $address;
    protected string $name;
    protected string $emailView;
    protected string $title;
    protected array $with;

    /**
     * @param User|null $user
     */
    public function __construct(User $user = null)
    {
        $this->address = config('mail.support');
        $this->name = config('app.name');
        $this->lang = $user->language ?? LangEnum::LANG_FRENCH;
    }

    /**
     * @return self
     */
    public function build(): self
    {
        $this->title = $this->getTitle();
        $this->emailView = $this->getView();
        $this->with['title'] = $this->title;

        return $this->from(
            address: config('mail.support'),
            name: ucfirst(config('app.name'))
        )
            ->subject($this->title)
            ->view("emails.$this->lang.$this->id")
            ->with($this->with);
    }

    /**
     * @return string
     */
    protected function getTitle(): string
    {
        return __(sprintf("mail.%s.title", $this->id), [], $this->lang);
    }

    /**
     * @return string
     */
    protected function getView(): string
    {
        return sprintf("emails.%s.%s", $this->lang, $this->id);
    }
}