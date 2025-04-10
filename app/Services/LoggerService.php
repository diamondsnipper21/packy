<?php

namespace App\Services;

use App\Mail\ExceptionMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LoggerService
{
    /**
     * @param string $method
     * @param int $line
     * @param string $message
     * @return void
     */
    public static function logException(string $method, int $line, string $message): void
    {
        Log::channel('exceptions')->error("$method L$line", [
            'user' => auth()->user()->id ?? '',
            'message' => $message
        ]);

        Mail::to('contact@studio27.fr')->send(new ExceptionMail($message));
    }
}
