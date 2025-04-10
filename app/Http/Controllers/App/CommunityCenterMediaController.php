<?php

namespace App\Http\Controllers\App;

use App\Helpers\BrowserHelper;
use App\Models\ResourceFile;
use App\Services\MediaService;
use App\Services\ResourceFileService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Response;

class CommunityCenterMediaController extends AppController
{
    /**
     * Uploads a file to medias folder
     *
     * @param Request $request
     * @param MediaService $mediaService
     * @return array
     */
    public function upload(
        Request $request,
        MediaService $mediaService
    ): array
    {
        $request->validate([
            'from' => 'string',
            'filetype' => 'string',
            'file' => 'array',
            'size' => 'numeric'
        ]);

        $from = $request->input('from', '');
        $filetype = $request->input('filetype', '');
        $file = $request->file('file');
        $size = $request->input('size');

        ini_set('memory_limit', '10G');

        $response = [];
        if (is_array($file)) {
            foreach ($file as $oneFile) {
                $response[] = $mediaService->uploadMediaToAws($oneFile, $from, $filetype, $size);
            }
        } else {
            $response = $mediaService->uploadMediaToAws($file, $from, $filetype, $size);
        }

        return $response;
    }

    /**
     * Displays the content of a resource file based on its UUID
     *
     * @param Request $request
     * @param ResourceFileService $resourceFileService
     */
    public function view(Request $request, ResourceFileService $resourceFileService)
    {
        $resourceFile = ResourceFile::where(['uuid' => $request->uuid])->first();
        if (!$resourceFile/* || !$resourceFileService->isPublicResourceFile($resourceFile)*/) {
            return response(__('No resource found.'), 404);
        }

        try {
            $client = new Client();
            $response = $client->get($resourceFile->url, ['stream' => true]);

            if (BrowserHelper::isSafari() === true) {
                return $response;
            }

            return response($response->getBody()->getContents(), $response->getStatusCode())
                ->header('Content-Type', $resourceFile->mime_type);
        } catch (GuzzleException $e) {
            //LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response(__('Server error'), 500);
        } catch (\Exception $e) {
            //LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return response(__('Server error'), 500);
        }
    }
}
