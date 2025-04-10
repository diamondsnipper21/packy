<?php

namespace Database\Seeders;

use App\Models\Extensions;
use App\Services\LoggerService;
use Illuminate\Database\Seeder;

class ExtensionsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $extensions = [
            [
                'id' => 1,
                'name' => __('Membership questions'),
                'description' => __('Ask members questions when they request access to your group.'),
                'type' => 'membership_question',
            ],
            [
                'id' => 2,
                'name' => __('Auto DM new members'),
                'description' => __('Send an automated DM to new members.'),
                'type' => 'auto_dm',
            ],
            [
                'id' => 3,
                'name' => __('Unlock chat'),
                'description' => __('Reduce DM spam by requiring members to be at Level 2 to chat.'),
                'type' => 'unlock_chat',
            ],
            [
                'id' => 4,
                'name' => __('Facebook'),
                'description' => __('Facebook Pixel ID'),
                'type' => 'facebook_pixel',
            ],
        ];

        foreach ($extensions as $extension) {
            try {
                $ext = Extensions::firstOrNew(['id' => $extension['id']]);
                $ext->name = $extension['name'];
                $ext->description = $extension['description'];
                $ext->type = $extension['type'];
                $ext->save();
            } catch (\Exception $e) {
                LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
            }
        }
    }
}
