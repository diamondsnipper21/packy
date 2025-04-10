<?php

namespace App\Http\Controllers\App;

use App\Models\CommunityCategory;
use App\Services\LoggerService;
use Illuminate\Http\Request;

class CommunityCategoriesController extends AppController
{
    /**
     * @param Request $request
     * @return array
     */
    public function save(Request $request): array
    {
        $id = $request->id ?? 0;
        if ($id == 0) {
            $category = new CommunityCategory();
        } else {
            $category = CommunityCategory::find($id);
            if (!$category) {
                return ['success' => false, 'message' => __('Community category not found')];
            }
        }

        try {
            $category->community_id = $request->community_id ?? 0;
            $category->title = $request->title ?? '';
            $category->admin_only = $request->admin_only ?? 0;
            $category->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'categories' => CommunityCategory::where(['community_id' => $category->community_id])->orderBy('created_at', 'asc')->get()
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function delete(Request $request): array
    {
        try {
            CommunityCategory::where(['community_id' => $request->community_id, 'id' => $request->id])->delete();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }

        return [
            'success' => true,
            'categories' => CommunityCategory::where(['community_id' => $request->community_id])->orderBy('created_at', 'asc')->get()
        ];
    }
}