<?php
namespace App\Http\Controllers\App;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PrivacyController extends AppController
{
    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        #Hide the legal pages for now, redirect the pages to the website homepage
        #https://trello.com/c/iujVmP9h/612-various-fixes

        /*
        $auth = false;
        $userId = session('user_id');

        // set user locale for translations
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $auth = true;
                if ($user->language) {
                    app()->setLocale($user->language);
                }
            }
        }

        return view('dashboard', [
            'auth' => $auth,
            'action' => 'legal',
            'userId' => $userId
        ]);
        */

        return redirect()->route('home');
    }
}
