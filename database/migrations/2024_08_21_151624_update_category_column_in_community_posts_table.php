<?php

use App\Models\CommunityCategory;
use App\Models\CommunityPost;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCategoryColumnInCommunityPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->after('pinned');
        });

        $this->updateCategoryColumnValues();

        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropColumn('category_id');
            $table->string('category')->after('pinned');
        });
    }

    /**
     * @return void
     */
    private function updateCategoryColumnValues(): void
    {
        $posts = CommunityPost::all();
        if (!empty($posts)) {
            foreach ($posts as $post) {
                $communityId = $post->community_id;
                $category = $post->category;

                $communityCategory = CommunityCategory::where([
                    'community_id' => $communityId,
                    'title' => $category
                ])->first();

                if (!empty($communityCategory)) {
                    try {
                        $post->category_id = $communityCategory->id;
                        $post->save();
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }
        }
    }
}
