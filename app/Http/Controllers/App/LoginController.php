<?php

namespace App\Http\Controllers\App;

use App\Helpers\TextHelper;
use App\Models\Community;
use App\Models\User;
use App\Services\CommunityService;
use App\Services\MemberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends AppController
{
    /**
     * @param Request $request
     * @param CommunityService $communityService
     * @param MemberService $memberService
     * @return array
     */
    public function doLogin(Request $request, CommunityService $communityService, MemberService $memberService): array
    {
        $validator = \Validator($request->all(), [
            'email' => ['required', 'email'],
            'password' => 'required'
        ],[
            'email.required' => __('Email is required.'),
            'email.email' => __('Email should be a valid email address.'),
            'password.required' => __('Password is required.')
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->first()
            ];
        }

        $userData = [
            'email' => TextHelper::removeSpecialChars($request->email),
            'password' => TextHelper::removeSpecialChars($request->password)
        ];

        if (!Auth::attempt($userData)) {
            return ['success' => false, 'message' => __('The provided credentials do not match our records.')];
        }

        $user = User::where(['email' => $userData['email']])
            ->with('member')
            ->first();

        $validUrl = $communityService->getLatestUrl();

        $inviteToken = $request->inviteToken ?? null;
        if ($inviteToken) {
            $communityUrl = $memberService->addUserToCommunityByToken($request->all(), $user);
            $validUrl = !empty($communityUrl) ? $communityUrl : $validUrl;
        }

        session()->put('user_id', $user->id);

        return [
            'success' => true,
            'user' => $user,
            'validUrl' => $validUrl,
            'incubateurMemberExist' => $memberService->isUserIncubateurCommunityMember($user->id)
        ];
    }

    /**
     * Log the user out
     *
     * - record datetime logged out
     * - invalidate session
     * - redirect to login
     */
    public function doLogout(): array
    {
        session()->forget('user_id');
        session()->forget('collapsed_set_ids');
        auth()->logout();

        return ['success' => true];
    }

    /**
     * Validate community url
     *
     * @param Request $request
     * @return array
     */
    public function validateUrl(Request $request): array
    {
        $validator = \Validator($request->all(), [
            'url' => 'required',
        ],[
            'url.required' => __('Community url should be provided.'),
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->first()
            ];
        }

        $community = Community::where(['url' => $request->url])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found')];
        }

        return ['success' => true];
    }
}
