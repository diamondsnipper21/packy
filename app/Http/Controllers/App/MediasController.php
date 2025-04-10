<?php

namespace App\Http\Controllers\App;

use App\Http\Requests\Medias\MediaDeleteRequest;
use App\Http\Requests\Medias\MediaOrderUpdateRequest;
use App\Http\Requests\Medias\MediaRequest;
use App\Models\Community;
use App\Models\Medias;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;

/**
 * Class MediasController
 *
 * @package App\Http\Controllers\App
 */
class MediasController extends AppController
{
    /**
     * @param MediaRequest $request
     * @param MediaService $mediaService
     * @return JsonResponse
     */
    public function save(MediaRequest $request, MediaService $mediaService): JsonResponse
    {
        $postData = $request->validated();

        $community = $postData['community'];
        if (!$community) {
            return response()->json([
                'success' => false,
                'message' => __('Community not found')
            ], 400);
        }

        $mediaService->createNewMedia(
            path: $postData['path'],
            type: $postData['type'],
            owner: Medias::OWNER_COMMUNITY,
            ownerId: $community->id
        );

        return response()->json([
            'success' => true,
            'data' => Community::getCommunity($community->id)
        ]);
    }

    /**
     * Remove Community Media
     *
     * @param MediaDeleteRequest $request
     * @param MediaService $mediaService
     * @return JsonResponse
     */
    public function delete(MediaDeleteRequest $request, MediaService $mediaService): JsonResponse
    {
        $postData = $request->validated();

        $community = $postData['community'];
        if (!$community) {
            return response()->json([
                'success' => false,
                'message' => __('Community not found')
            ], 400);
        }

        $mediaService->deleteMedia(
            owner: Medias::OWNER_COMMUNITY,
            ownerId: $community->id,
            mediaId: $postData['mediaId']
        );

        return response()->json([
            'success' => true,
            'data' => Community::getCommunity($community->id)
        ]);
    }

    /**
     * Update the order of media items within a community
     *
     * @param MediaOrderUpdateRequest $request The request containing the updated media order information
     * @param MediaService $mediaService The media service for handling the order update
     * @return JsonResponse JSON response indicating the success of the operation
     */
    public function updateOrder(MediaOrderUpdateRequest $request, MediaService $mediaService): JsonResponse
    {
        $postData = $request->validated();

        $community = $postData['community'];
        if (!$community) {
            return response()->json([
                'success' => false,
                'message' => __('Community not found')
            ], 400);
        }

        $from = $postData['from'];
        $to = $postData['to'];

        $mediaService->changeMediaOrder(
            communityId: $community->id,
            fromId: $from['id'],
            toId: $to['id']
        );

        return response()->json(['success' => true]);
    }
}
