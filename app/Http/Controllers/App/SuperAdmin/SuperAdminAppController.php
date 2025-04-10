<?php

namespace App\Http\Controllers\App\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class SuperAdminAppController extends Controller
{
    /**
     * @return View
     */
    public function home(): View
    {
        return view('dashboard-super-admin', [
            'auth' => (bool) session('user_id'),
        ]);
    }
}