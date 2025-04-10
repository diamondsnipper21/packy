<?php

namespace App\Http\Controllers\App;

use App\Enum\LangEnum;
use App\Services\LoggerService;
use Illuminate\Http\Request;

class LangController extends AppController
{
    /**
     * @param Request $request
     * @return array
     */
    public function update(Request $request): array
    {
        $language = $request->language ?? LangEnum::LANG_ENGLISH;

        $user = auth()->user();
        if (!$user) {
            return ['success' => false, 'message' => __('User not found.')];
        }

        try {
            $user->language = $language;
            $user->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        session(['lang' => $language]);

        return ['success' => true];
    }
}
