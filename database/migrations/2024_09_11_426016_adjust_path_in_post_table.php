<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CommunityPost;
use App\Models\ScheduledPost;
use Illuminate\Support\Facades\DB;

class AdjustPathInPostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->updateCommunityPost();
        $this->updateScheduledPost();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {}


    /**
     * @return void
     */
    private function updateCommunityPost(): void
    {
        $duplicate_path = DB::table('community_posts')
            ->selectRaw('count(*) as post_num, path')
            ->groupBy('path')
            ->having('post_num', '>', 1)
            ->get();

        foreach($duplicate_path as $item){
            $posts = CommunityPost::where('path', $item->path)->get();
            foreach($posts as $post){
                $post->path = $post->path . '-' . mt_rand(1000, 9999);
                $post->save();
            }
        }
    }

    private function updateScheduledPost(): void
    {
        $duplicate_path = DB::table('scheduled_posts')
            ->selectRaw('count(*) as post_num, path')
            ->groupBy('path')
            ->having('post_num', '>', 1)
            ->get();

        foreach($duplicate_path as $item){
            $posts = ScheduledPost::where('path', $item->path)->get();
            foreach($posts as $post){
                $post->path = $post->path . '-' . mt_rand(1000, 9999);
                $post->save();
            }
        }
    }
}
