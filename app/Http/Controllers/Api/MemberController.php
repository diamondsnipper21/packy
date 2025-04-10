<?php

namespace App\Http\Controllers\Api;

use App\Helpers\TextHelper;
use App\Http\Controllers\Controller;
use App\Models\CommunityMember;
use App\Models\User;
use App\Services\LoggerService;
use App\Services\MemberService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $request->community->members]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function view(Request $request): JsonResponse
    {
        $member = CommunityMember::where([
            'community_id' => $request->community->id,
            'id' => $request->memberId
        ])->with('member')->first();

        if (!$member) {
            return response()->json(['success' => false, 'message' => __('No found member.')], 404);
        }

        return response()->json(['success' => true, 'data' => $member->member]);
    }

    /**
     * @param Request $request
     * @param MemberService $memberService
     * @param UserService $userService
     * @return JsonResponse
     */
    public function create(Request $request, MemberService $memberService, UserService $userService): JsonResponse
    {
        $validator = \Validator($request->all(), [
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => ['required', 'email'],
            'country' => 'required|max:2'
        ],[
            'firstname.required' => __('Firstname is required.'),
            'lastname.required' => __('Lastname is required.'),
            'email.required' => __('Email is required.'),
            'email.email' => __('Email should be a valid email address.'),
            'country.required' => __('Country is required.')
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $email = TextHelper::removeSpecialChars($request->email);

        // create user
        $user = User::where(['email' => $email])->first();
        if (!$user) {
            try {
                $user = User::create([
                    'email' => $email,
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'password' => bcrypt(Str::random(32)),
                    'last_activity' => date('Y-m-d H:i:s')
                ]);
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
        }

        $requestData = $request->all();
        $requestData['communityId'] = $request->community->id;
        $requestData['memberAccess'] = CommunityMember::ACCESS_ALLOWED;

        // update user + create community member
        try {
            $userService->updateUser($user, $requestData);
            $memberService->addUserToCommunity(
                $request->community->id,
                $user->id,
                CommunityMember::ACCESS_ALLOWED
            );
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json(['success' => true, 'data' => $member]);
    }

    /**
     * @param Request $request
     * @param MemberService $memberService
     * @return JsonResponse
     */
    public function update(Request $request, MemberService $memberService): JsonResponse
    {
        $validator = \Validator($request->all(), [
            'memberId' => 'required'
        ],[
            'memberId.required' => __('Member not found.'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 400);
        }

        $member = CommunityMember::where(['community_id' => $request->community->id, 'id' => $request->memberId])->first();
        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => __('No found member.')
            ], 404);
        }

        $user = $member->user;

        $tag = $request->tag ?? $user->tag;
        if (!$tag) {
            $tag = $memberService->generateUniqTag($user->name);
        }

        try {
            $user->firstname = $request->firstname ?? $user->firstname;
            $user->lastname = $request->lastname ?? $user->lastname;
            $user->tag = $tag;
            $user->bio = $request->bio ?? $user->bio;
            $user->photo = $request->photo ?? $user->photo;
            $user->link = $request->link ?? $user->link;
            $user->timezone = $request->timezone ?? $user->timezone;
            $user->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        return response()->json(['success' => true, 'data' => $member]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function remove(Request $request): JsonResponse
    {
        $member = CommunityMember::where(['community_id' => $request->community->id, 'id' => $request->memberId])->first();
        if (!$member) {
            return response()->json(['success' => false, 'message' => __('Member not found.')], 404);
        }

        if ($request->community->user_id === $member->user_id) {
            return response()->json(['success' => false, 'message' => __('Creator cannot be removed from community.')], 404);
        }

        try {
            $member->access = CommunityMember::ACCESS_REMOVED;
            $member->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response()->json(['success' => false]);
        }

        return response()->json(['success' => true]);
    }
}
