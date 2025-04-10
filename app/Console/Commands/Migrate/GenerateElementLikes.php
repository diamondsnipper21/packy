<?php

namespace App\Console\Commands\Migrate;

use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\ElementLike;
use Illuminate\Console\Command;

class GenerateElementLikes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migrate-element-likes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate likes from posts and comments table after table is added';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $likePosts = CommunityPost::whereNotNull('likes')->get();
        $likeData = [];
        foreach ($likePosts as $post) {
            $likeIds = explode(',', $post->likes);
            foreach ($likeIds as $memberId) {
                if (empty((int)$memberId)) continue;
                $likeData[] = [
                    'community_id' => $post->community_id,
                    'member_id' => $memberId,
                    'element_id' => $post->id,
                    'element_type' => ElementLike::POST,
                    'element_owner_id' => $post->member_id,
                    'status' => 1,
                    'created_at' => $post->created_at
                ];
            }
        }
        ElementLike::insert($likeData);

        $likeComments = CommunityPostComment::whereNotNull('likes')->with('post')->get();
        $likeData = [];
        foreach ($likeComments as $comment) {
            $likeIds = explode(',', $comment->likes);
            foreach ($likeIds as $memberId) {
                if (empty((int)$memberId)) continue;
                $likeData[] = [
                    'community_id' => $comment->post->community_id,
                    'member_id' => $memberId,
                    'element_id' => $comment->id,
                    'element_type' => ElementLike::COMMENT,
                    'element_owner_id' => $comment->member_id,
                    'status' => 1,
                    'created_at' => $comment->created_at
                ];
            }
        }
        ElementLike::insert($likeData);
    }
}
