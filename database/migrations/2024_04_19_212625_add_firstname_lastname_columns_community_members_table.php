<?php

use App\Models\CommunityMember;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFirstnameLastnameColumnsCommunityMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('community_members', function (Blueprint $table) {
            $table->string('firstname')->after('user_id');
            $table->string('lastname')->after('firstname');
        });

        $this->updateMembers();

        Schema::table('community_members', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('community_members', function (Blueprint $table) {
            $table->dropColumn('lastname');
            $table->dropColumn('firstname');

            $table->string('name')->after('user_id');
        });
    }

    /**
     * @return void
     */
    private function updateMembers(): void
    {
        $members = CommunityMember::all();
        if (!empty($members)) {
            foreach ($members as $member) {
                $name = $member->name;
                $parts = explode(' ', $name);
                $lastname = array_pop($parts);
                $firstname = implode(' ', $parts);

                try {
                    $member->firstname = $firstname;
                    $member->lastname = $lastname;
                    $member->save();
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    }
}
