<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UnreadIntervalMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $language;
    public int $count;
    public array $summaryInfos;

    /**
     * Create a new message instance.
     *
     * @param string $language
     * @param int $count
     * @param array $summaryInfos
     */
    public function __construct(string $language, int $count, array $summaryInfos)
    {
        $this->language = $language;
        $this->count = $count;
        $this->summaryInfos = $summaryInfos;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->count === 1) {
            $title = __('mail.unread-interval.title-1', ['count' => $this->count], $this->language);
            $desc = __('mail.unread-interval.desc-1', ['count' => $this->count], $this->language);
        } else {
            $title = __('mail.unread-interval.title-2', ['count' => $this->count], $this->language);
            $desc = __('mail.unread-interval.desc-2', ['count' => $this->count], $this->language);
        }

        return $this->from(
            address: config('mail.support'),
            name: ucfirst(config('app.name'))
        )
            ->subject($title)
            ->view("emails.$this->language.unread-interval")
            ->with([
                'title' => $title,
                'summaryInfos' => $this->summaryInfos,
                'desc' => $desc,
                'date' => __('mail.unread-interval.date', ['date' => date("D, M j Y")], $this->language),
            ]);
    }
}
