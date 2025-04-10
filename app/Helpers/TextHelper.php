<?php

namespace App\Helpers;

class TextHelper
{
    /**
     * @param string $content
     * @param array $mentionedMembers
     * @return string
     */
    public static function insertMention(string $content, array $mentionedMembers): string
    {
        foreach ($mentionedMembers as $mentionedMember) {
            $tagExpression = trim($mentionedMember['tag']);
            $content = str_replace(
                $tagExpression,
                '<span class="mentioned-name" data-id="' . trim($mentionedMember['id']) . '">' . $tagExpression . '</span>',
                $content
            );
        }

        return $content;
    }

    /**
     * Remove special chars
     *
     * @param string $string
     * @return string
     */
    public static function removeSpecialChars(string $string): string
    {
        return preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', trim($string));
    }

    /**
     * Prevent special chars
     *
     * @param string $string
     * @return string
     */
    public static function preventSpecialChars(string $string): string
    {
        $string = strtolower(trim($string));
        $string = str_replace(' ', '', $string);

        return preg_replace('/[^a-z0-9\-]/', '', $string);
    }
}