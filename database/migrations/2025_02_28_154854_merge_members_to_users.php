<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MergeMembersToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->migrateUserData();

        Schema::table('community_has_members', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('community_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        $communityMembers = DB::table('community_has_members')->get();
        foreach ($communityMembers as $communityMember) {
            $member = DB::table('community_members')->find($communityMember->member_id);
            if (!$member) {
                continue;
            }

            DB::table('community_has_members')
                ->where('id', $communityMember->id)
                ->update(['user_id' => $member->user_id]);
        }

        Schema::table('community_has_members', function (Blueprint $table) {
            $table->unique(['community_id', 'user_id']);
        });

        Schema::table('element_likes', function (Blueprint $table) {
            $table->unsignedBigInteger('community_id')->nullable()->change();
            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
        });


        Schema::table('invite_user_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('community_id')->nullable()->change();
        });
        \App\Models\InviteUserTokens::where(['community_id' => 0])->update(['community_id' => NULL]);
        Schema::table('invite_user_tokens', function (Blueprint $table) {
            $table->foreign('community_id')->references('id')->on('community')->onDelete('cascade');
        });


        // member_id -> has_member_id
        $this->migrateMemberId();

        // member_id -> user_id
        $this->migrateMemberIdToUserId();

        // user_id -> has_member_id
        $this->migrateUserIdToMemberId();


        Schema::table('community_has_members', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->dropColumn('member_id');
        });

        Schema::drop('community_members');
        Schema::rename('community_has_members', 'community_members');
        Schema::rename('payment_methods', 'payment_methods_marketplace');
    }


    private function migrateUserData(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_activity')->nullable()->after('stripe_customer_id');
            $table->string('timezone')->nullable()->after('stripe_customer_id');
            $table->text('bio')->nullable()->after('stripe_customer_id');
            $table->string('link')->nullable()->after('stripe_customer_id');
            $table->string('photo')->nullable()->after('stripe_customer_id');
            $table->string('tag')->nullable()->after('stripe_customer_id');
            $table->string('stripe_customer_id_marketplace')->nullable()->after('stripe_customer_id');
        });

        $users = DB::table('users')->get();
        foreach ($users as $user) {
            $member = DB::table('community_members')->where(['user_id' => $user->id])->first();
            if (!$member) {
                continue;
            }

            DB::table('users')->where(['id' => $user->id])->update([
                'last_activity' => $member->last_activity,
                'timezone' => $member->timezone,
                'bio' => $member->content,
                'link' => $member->link,
                'photo' => $member->photo,
                'tag' => $member->tag,
                'country' => $member->country
            ]);
        }

        Schema::table('community_members', function (Blueprint $table) {
            $table->dropColumn('firstname');
            $table->dropColumn('lastname');
            $table->dropColumn('country');
            $table->dropColumn('tag');
            $table->dropColumn('content');
            $table->dropColumn('photo');
            $table->dropColumn('link');
            $table->dropColumn('stripe_customer_id');
            $table->dropColumn('last_activity');
            $table->dropColumn('timezone');
        });
    }

    private function migrateMemberId(): void
    {
        // $communityMembers = \App\Models\CommunityMember::all();
        $communityMembers = DB::table('community_has_members')->get();

        foreach (['api_keys', 'auto_dm_templates', 'community_classrooms_lessons_completed', 'community_member_settings',
                     'community_member_subscriptions_cancel_requests', 'community_group_members', 'digest_posts_sent',
                     'scheduled_posts', 'unread_notifications_sent', 'element_likes', 'community_posts', 'community_post_comments'] as $table)
        {
            $foreignName = null;
            if ($table === 'community_classrooms_lessons_completed') {
                $foreignName = 'fk_cclc';
            }
            if ($table === 'community_member_subscriptions_cancel_requests') {
                $foreignName = 'fk_cmscr';
            }
            if ($table === 'community_member_subscriptions_transactions') {
                $foreignName = 'fk_cmst';
            }

            Schema::table($table, function (Blueprint $table) use ($foreignName) {
                $table->unsignedBigInteger('has_member_id')->nullable()->after('id');
                $table->foreign('has_member_id', $foreignName)->references('id')->on('community_has_members')->onDelete('cascade');
            });

            if ($table === 'community_post_comments') {
                $comments = DB::table('community_post_comments')->get();
                foreach ($comments as $comment) {
                    $post = DB::table('community_posts')->find($comment->post_id);
                    if (!$post) {
                        continue;
                    }

                    $communityMember = DB::table('community_has_members')->where([
                        'community_id' => $post->community_id,
                        'member_id' => $comment->member_id
                    ])->first();

                    if (!$communityMember) {
                        continue;
                    }

                    DB::table($table)
                        ->where('id', $comment->id)
                        ->update(['has_member_id' => $communityMember->id]);
                }
            } else if ($table === 'community_group_members') {
                $groupMembers = \App\Models\CommunityGroupMembers::with('group')->with('group.community')->get();
                foreach ($groupMembers as $groupMember) {
                    $communityMember = DB::table('community_has_members')->where([
                        'community_id' => $groupMember->group->community->id,
                        'member_id' => $groupMember->member_id
                    ])->first();

                    if (!$communityMember) {
                        continue;
                    }

                    DB::table($table)
                        ->where('id', $groupMember->id)
                        ->update(['has_member_id' => $communityMember->id]);
                }
            } else {
                foreach ($communityMembers as $communityMember) {
                    DB::table($table)
                        ->where('member_id', $communityMember->member_id)
                        ->where('community_id', $communityMember->community_id)
                        ->update(['has_member_id' => $communityMember->id]);
                }
            }

            $foreignKey = ['member_id'];
            if ($table === 'community_member_subscriptions_cancel_requests') {
                $foreignKey = 'cmscr_fk2';
            }
            if ($table === 'element_likes') {
                $foreignKey = null;
            }

            if ($foreignKey !== null) {
                Schema::table($table, function (Blueprint $table) use ($foreignKey) {
                    $table->dropForeign($foreignKey);
                });
            }

            Schema::table($table, function (Blueprint $table) use ($foreignKey) {
                $table->dropColumn('member_id');
                $table->renameColumn('has_member_id', 'member_id');
            });
        }
    }

    private function migrateMemberIdToUserId(): void
    {
        // $communityMembers = \App\Models\CommunityMember::all();
        $communityMembers = DB::table('community_members')->get();

        foreach (['community', 'notifications', 'community_waitlist', 'payment_methods'] as $table) {
            $action = 'cascade';
            if ($table === 'community') {
                $action = 'set null';
            }

            Schema::table($table, function (Blueprint $table) use ($action) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete($action);
            });

            foreach ($communityMembers as $communityMember) {
                DB::table($table)
                    ->where('member_id', $communityMember->id)
                    ->update(['user_id' => $communityMember->user_id]);
            }

            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['member_id']);
                $table->dropColumn('member_id');
            });
        }
    }

    private function migrateUserIdToMemberId(): void
    {
        foreach (['community_member_subscriptions', 'community_member_subscriptions_transactions'] as $table) {
            $foreign  = null;
            if ($table === 'community_member_subscriptions') {
                $foreign = 'cms_fk1';
            }
            if ($table === 'community_member_subscriptions_transactions') {
                $foreign = 'cmst_fk1';
            }

            Schema::table($table, function (Blueprint $table) use ($foreign) {
                $table->unsignedBigInteger('member_id')->nullable()->after('id');
                $table->foreign('member_id')->references('id')->on('community_has_members')->onDelete('cascade');

                $table->dropForeign($foreign);
                $table->dropColumn('user_id');
            });
        }
    }
}
