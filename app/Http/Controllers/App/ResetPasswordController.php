<?php

namespace App\Http\Controllers\App;

use App\Helpers\TextHelper;
use App\Models\User;
use App\Services\LoggerService;
use App\Services\MailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class ResetPasswordController extends AppController
{
    /**
     * @param Request $request
     * @param MailService $mailService
     * @return array
     */
    public function sendResetPassword(Request $request, MailService $mailService): array
    {
        $validator = \Validator($request->all(), [
            'email' => ['required', 'email']
        ],[
            'email.required' => __('Email is required.'),
            'email.email' => __('Email should be a valid email address.'),
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->first()
            ];
        }

        $email = TextHelper::removeSpecialChars($request->email);
        $url = $request->url ?? null;

        return $mailService->sendResetPasswordEmail($email, $url);
    }

    /**
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function receiveResetPassword(Request $request): View|RedirectResponse
    {
        $validator = \Validator($request->all(), [
            'token' => 'required'
        ],[
            'token.required' => __('Token is required.')
        ]);

        if ($validator->fails()) {
            return redirect()->to('/');
        }

        $url = $request->url ?? '';
        $redirection = '/';
        if ($url) {
            $redirection .= $url;
        }

        $user = User::where('token', $request->token)->first();
        if (!$user) {
            return redirect()->to($redirection);
        }

        try {
            $user->token = null;
            $user->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return redirect()->to($redirection);
        }

        return view('dashboard', [
            'auth' => false,
            'action' => 'reset-password',
            'communityUrl' => $url,
            'userId' => $user->id
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function resetPassword(Request $request): array
    {
        $validator = \Validator($request->all(), [
            'password' => 'required'
        ],[
            'password.required' => __('Password is required.')
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => $validator->errors()->first()
            ];
        }

        $password = TextHelper::removeSpecialChars($request->password);

        $user = User::find($request->userId);
        if (!$user) {
            return ['success' => false, 'message' => __('User is not found.')];
        }

        try {
            $user->password = bcrypt($password);
            $user->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return ['success' => true, 'message' => __('Password has been updated successfully!')];
    }
}
