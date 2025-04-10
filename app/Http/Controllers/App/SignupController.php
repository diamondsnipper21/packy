<?php

namespace App\Http\Controllers\App;

use App\Helpers\TextHelper;
use App\Services\CommunityService;
use App\Services\LoggerService;
use App\Models\User;
use App\Models\Visit;
use App\Services\MemberService;
use App\Services\SignupService;
use Illuminate\Http\Request;

class SignupController extends AppController
{
    private CommunityService $communityService;
    private MemberService $memberService;

    /**
     * @param CommunityService $communityService
     * @param MemberService $memberService
     */
    public function __construct(CommunityService $communityService, MemberService $memberService)
    {
        $this->communityService = $communityService;
        $this->memberService = $memberService;
    }
    
    /**
     * @param Request $request
     * @param SignupService $signupService
     * @return array
     */
    public function doSignup(Request $request, SignupService $signupService): array
    {
        $validator = \Validator($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => ['required', 'email'],
            'country' => 'required',
            'password' => 'required',
            'inviteToken' => '',
        ],[
            'firstname.required' => __('Firstname is required.'),
            'lastname.required' => __('Lastname is required.'),
            'email.required' => __('Email is required.'),
            'email.email' => __('Email should be a valid email address.'),
            'country.required' => __('Country is required.'),
            'password' => __('Password is required.'),
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->first(),
            ];
        }

        // @todo - move to service

        $email = TextHelper::removeSpecialChars($request->email);
        $password = TextHelper::removeSpecialChars($request->password);

        $existingUser = User::where(['email' => $email])->first();
        if ($existingUser) {
            return [
                'success' => false,
                'message' => __('The provided email already exists.'),
            ];
        }

        try {
            $user = new User();
            $user->email = $email;
            $user->firstname = $request->firstname;
            $user->lastname = $request->lastname;
            $user->password = bcrypt($password);
            $user->country = $request->country;
            $user->last_activity = date('Y-m-d H:i:s');
            $user->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        $communityId = $request->communityId ?? 0;
        $selectedTab = $request->selectedTab ?? '';
        if ($communityId > 0 && in_array($selectedTab, Visit::TRACKED_PAGES)) {
            $signupService->save($communityId, $user->id, $selectedTab);
        }
        
        auth()->login($user);
        session()->put('user_id', $user->id);

        $validUrl = $this->communityService->getLatestUrl();

        $inviteToken = $request->inviteToken ?? null;
        if ($inviteToken) {
            $communityUrl = $this->memberService->addUserToCommunityByToken($request->all(), $user);
        }

        $user = User::where(['id' => $user->id])
            ->with('member')
            ->with('paymentMethods')
            ->first();

        session(['lang' => $user->language]);

        return [
            'success' => true,
            'user' => $user,
            'validUrl' => $communityUrl ?? $validUrl,
        ];
    }
}
