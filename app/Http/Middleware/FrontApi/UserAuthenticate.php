<?php

namespace App\Http\Middleware\FrontApi;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAuthenticate
{
    /**
     * @param  Request  $request
     * @param  Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('user_id') ?? 0;

        $user = User::where(['id' => $userId])->first();
        if (!$user) {
            return redirect()->to('/');
        }
        
        $request->merge(['user' => $user]);

        return $next($request);
    }
}
