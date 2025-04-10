<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EventReminderMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $eventTitle;
    public string $eventStartAt;
    public string $eventTimezone;
    public string $eventDescription;
    public ?string $eventLink;
    public ?string $language;
    public string $replyToEmailAddress;

    /**
     * Create a new message instance.
     *
     * @param string $eventTitle
     * @param string $eventStartAt
     * @param string $eventTimezone
     * @param string $language
     * @param string $replyToEmailAddress
     * @param string|null $eventDescription
     * @param string|null $eventLink
     */
    public function __construct(
        string $eventTitle,
        string $eventStartAt,
        string $eventTimezone,
        string $language,
        string $replyToEmailAddress,
        ?string $eventDescription,
        ?string $eventLink
    ) {
        $this->eventTitle = $eventTitle;
        $this->eventStartAt = $eventStartAt;
        $this->eventTimezone = $eventTimezone;
        $this->eventDescription = $eventDescription;
        $this->eventLink = $eventLink;
        $this->language = $language;
        $this->replyToEmailAddress = $replyToEmailAddress;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $title = __('mail.event.subject', ['event' => $this->eventTitle], $this->language);

        return $this->from(
            address: config('mail.support'),
            name: ucfirst(config('app.name'))
        )
        ->replyTo($this->replyToEmailAddress)
        ->subject($title)
        ->view("emails.$this->language.event-reminder")
        ->with([
            'title' => $title,
            'eventTitle' => $this->eventTitle,
            'eventStartAt' => $this->eventStartAt,
            'eventTimezone' => $this->eventTimezone,
            'eventDescription' => $this->eventDescription,
            'eventLink' => $this->eventLink,
        ]);
    }
}
