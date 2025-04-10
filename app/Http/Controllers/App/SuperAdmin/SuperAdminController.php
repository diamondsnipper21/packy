<?php

namespace App\Http\Controllers\App\SuperAdmin;

use App\Models\User;

class SuperAdminController extends SuperAdminAppController
{
    /**
     * @return array
     */
    public function getData(): array
    {
        $user = null;
        if (session('user_id')) {
            $user = User::getUserInfo();
        }

        return [
            'success' => true,
            'user' => $user
        ];
    }
}
