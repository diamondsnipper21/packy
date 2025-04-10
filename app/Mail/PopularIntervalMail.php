<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PopularIntervalMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $language;
    public string $period;
    public array $summaryInfos;

    /**
     * Create a new message instance.
     *
     * @param string $language
     * @param string $period
     * @param array $summaryInfos
     */
    public function __construct(string $language, string $period, array $summaryInfos)
    {
        $this->language = $language;
        $this->period = $period;
        $this->summaryInfos = $summaryInfos;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $title = __('mail.popular-interval.subject.' . $this->period, [], $this->language);

        return $this->from(
            address: config('mail.support'),
            name: ucfirst(config('app.name'))
        )
        ->subject($title)
        ->view("emails.$this->language.popular-interval")
        ->with([
            'title' => $title,
            'summary_infos' => $this->summaryInfos,
            'date' => __('mail.popular-interval.date', ['date' => date("D, M j Y")], $this->language),
        ]);
    }
}
