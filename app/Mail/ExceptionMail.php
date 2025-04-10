<?php

namespace App\Mail;

class ExceptionMail extends SupportMail
{
    /**
     * @param string $exception
     */
    public function __construct(string $exception)
    {
        parent::__construct();

        $this->id = 'exception-email';
        $this->with = [
            'exception' => $exception
        ];
    }
}
