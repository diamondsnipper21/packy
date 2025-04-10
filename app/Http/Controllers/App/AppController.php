<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class AppController extends Controller
{
    public function home(): View
    {
        $auth = false;
        if (auth()->check() === true) {
            $auth = true;
        }

        return view('dashboard', ['auth' => $auth]);
    }

    /**
     * Returns code 200 (used for heath check status)
     */
    public function healthCheckStatus(): void
    {
        http_response_code(200);
    }
}