<?php

namespace App\Support\Traits;
use Illuminate\Support\Str;

trait GenerateUniqueSlugTrait
{
    public static function bootGenerateUniqueSlugTrait(): void
    {
        static::saving(function ($model) {
            $path = $model->path;
            $model->path = $model->generateUniqueSlug($path);
        });
    }

    public function generateUniqueSlug(string $slug): string
    {
        // Check if the slug already has a number at the end
        $originalSlug = $slug;
        $slugNumber = null;

        if (preg_match('/-(\d+)$/', $slug, $matches)) {
            $slugNumber = $matches[1];
            $slug = Str::replaceLast("-$slugNumber", '', $slug);
        }

        // Check if the modified slug already exists in the table
        $existingSlugs = $this->getExistingSlugs($slug, $this->getTable());

        if (!in_array($slug, $existingSlugs)) {
            // Slug is unique, no need to append numbers
            return $slug . ($slugNumber ? "-$slugNumber" : '');
        }

        // Increment the number until a unique slug is found
        $i = $slugNumber ? ($slugNumber + 1) : 1;
        $uniqueSlugFound = false;

        while (!$uniqueSlugFound) {
            $newSlug = $slug . '-' . $i;

            if (!in_array($newSlug, $existingSlugs)) {
                // Unique slug found
                return $newSlug;
            }

            $i++;
        }

        // Fallback: return the original slug with a random number appended
        return $originalSlug . '-' . mt_rand(1000, 9999);
    }

    private function getExistingSlugs(string $path, string $table): array
    {
        return $this->where('path', 'LIKE', $path . '%')
            ->where('id', '!=', $this->id ?? null) // Exclude the current model's ID
            ->pluck('path')
            ->toArray();
    }
}