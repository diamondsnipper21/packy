<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminAuthenticate
{
    /**
     * Check if logged user is a super admin (Packie's admin)
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id') ?? 0;

        $user = User::where(['id' => $userId])->first();
        if ($user && $user->email === 'h.kevin@loupfute.com') {
            return $next($request);
        }

        return redirect()->to('/');
    }
}
