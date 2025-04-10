<?php

namespace App\Http\Controllers\App;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\InviteUserTokens;
use App\Models\User;
use App\Services\CommunityService;
use App\Services\MailService;
use App\Services\MemberService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use App\Exceptions\Jobs\SendAutoDmForJoinRequest;

class CommunityCenterInviteController extends AppController
{
    /**
     * @param Request $request
     * @return array
     */
    public function get(Request $request): array
    {
        $community = Community::where(['url' => $request->communityUrl])->first();
        if (!$community) {
            return ['success' => false, 'message' => __('Community not found.')];
        }

        $member = CommunityMember::where(['user_id' => $request->userId])->with('user')->first();
        if (!$member) {
            return ['success' => false, 'message' => __('Member not found.')];
        }

        return [
            'success' => true,
            'community' => $community,
            'member' => $member,
            'referUser' => $member->user
        ];
    }

    /**
     * Sends an email invitation to a user to join a community
     *
     * @param Request $request
     * @param MailService $mailService
     * @return array
     */
    public function send(Request $request, MailService $mailService): array
    {
        return $mailService->sendInviteEmail(
            $request->communityId,
            $request->memberId,
            $request->email
        );
    }

    /**
     * Handler for invite token for community
     *
     * @param Request $request
     * @param MemberService $memberService
     * @return RedirectResponse
     */
    public function tokenJoin(Request $request, MemberService $memberService): RedirectResponse
    {
        $validator = \Validator($request->all(), [
            'token' => 'required',
            'url' => 'required'
        ],[
            'token.required' => __('Token is required.'),
            'url.required' => __('Url is required.')
        ]);

        if ($validator->fails()) {
            return redirect()->to('/');
        }

        $request->validate([
            'token' => 'required',
            'url' => 'required'
        ]);

        $userId = session('user_id') ?? 0;
        $community = Community::where(['url' => $request->url])->first();
        $tokenRow = InviteUserTokens::where('token', $request->token)->first();
        if (!$community || !$tokenRow || ($tokenRow->community_id && $tokenRow->community_id != $community->id && !$tokenRow->auto_approval)) {
            return redirect()->to('/' . $request->url);
        }

        $user = User::where(['id' => $userId])->first();
        if ($user) {
            $accessArray = [CommunityMember::ACCESS_ALLOWED, CommunityMember::ACCESS_PENDING];
            $accessValue = CommunityMember::ACCESS_PENDING;

            // if email invite, then approve automatically, if url invite, then set to pending
            if ($tokenRow->auto_approval) {
                $accessArray = [CommunityMember::ACCESS_ALLOWED];
                $accessValue = CommunityMember::ACCESS_ALLOWED;
            }

            $member = CommunityMember::where([
                'community_id' => $community->id,
                'user_id' => $user->id
            ])
            ->whereIn('access', $accessArray)
            ->first();

            if (!$member) {
                $memberService->addUserToCommunity($community->id, $user->id, $accessValue);

                if ($accessValue === CommunityMember::ACCESS_ALLOWED) {
                    // Generate auto dm for join request
                    dispatch (new SendAutoDmForJoinRequest(
                        $community->id,
                        $user->id
                    ))->onQueue('send-auto-dm-for-join-request');
                }
            }
            return redirect()->to('/' . $request->url);
        }

        session()->forget('user_id');
        auth()->logout();

        $user = User::find($tokenRow->user_id);
        if (!$user) {
            return redirect()->to('/' . $request->url);
        }

        return redirect()->to('join')->with([
            'communityId' => $community->id,
            'userId' => $user->id,
            'inviteToken' => $request->token
        ]);
    }

    /**
     * Show the login or create password page for a Customer to enter Community
     * This method also controls the signup page (for some reason) by passing $settings->signup as true/false
     *
     * @return View|RedirectResponse
     */
    public function join(): View|RedirectResponse
    {
        $community = Community::find(session('communityId'));
        if (!$community) {
            return redirect()->to('/');
        }

        $user = User::find(session('userId'));
        if (!$user) {
            return redirect()->to('/' . $community->url);
        }

        return view('dashboard', [
            'auth' => false,
            'action' => 'invite',
            'communityUrl' => $community->url,
            'userId' => session('userId'),
            'inviteToken' => session('inviteToken'),
        ]);
    }

    /**
     * @param Request $request
     * @param CommunityService $communityService
     * @return array
     */
    public function getInviteShareLink(Request $request, CommunityService $communityService): array
    {
        return $communityService->getInviteShareLink($request->communityId, $request->memberId);
    }
}
