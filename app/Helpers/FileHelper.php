<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class FileHelper
{
    /**
     * Gets the extension type for a file
     *
     * @param string $extension
     * @return string
     */
    public static function getExtensionType(string $extension): string
    {
        switch (strtolower($extension)) {
            case 'jpg':
            case 'png':
            case 'gif':
            case 'jpeg':
                return 'image';

            case 'mp4':
            case 'mov':
            case 'qt':
            case 'avi':
            case 'wmv':
            case 'flv':
            case 'm4v':
            case 'webm':
                return 'video';

            case 'pdf':
                return 'pdf';

            case 'mp3':
            case 'wav':
            case 'aac':
            case 'm4a':
            case 'ogg':
                return 'audio';

            default:
                return 'file';
        }
    }

    /**
     * @param string $extension
     * @return string
     */
    public static function generateFileName(string $extension): string
    {
        $uuid = (string) Str::uuid();

        return $uuid . '.' . $extension;
    }
}
