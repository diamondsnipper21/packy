<?php

namespace App\Services;

class StorageService extends Service
{
    /**
     * Uploads a file to AWS server
     *
     * @param string $s3Path
     * @param string $file
     * @param int|null $size
     * @param string $public
     * @return array
     */
    public function uploadFileToAws(string $s3Path, string $file, ?int $size = null, string $public = ''): array
    {
        try {
            $s3 = \Storage::disk('s3');

            // file_get_contents puts the file into memory -> ok for smaller files (< 0.1 GB)
            if ($size && $size < 858993459) {
                $s3->put($s3Path, file_get_contents($file), $public);
            } else {
                $s3->put($s3Path, fopen($file, 'r'), $public); // use Stream
            }
            $awsPath = $s3->url($s3Path);
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            return $this->fail(__('An error occurred while uploading the file to AWS.'));
        }

        return $this->success(['awsPath' => $awsPath]);
    }
}
