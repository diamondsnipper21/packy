<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFirstnameLastnameColumnsUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('firstname')->after('id');
            $table->string('lastname')->after('firstname');
        });

        $this->updateUsers();

        Schema::table('users', function (Blueprint $table) {
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('lastname');
            $table->dropColumn('firstname');

            $table->string('name')->after('id');
        });
    }

    /**
     * @return void
     */
    private function updateUsers(): void
    {
        $users = User::all();
        if (!empty($users)) {
            foreach ($users as $user) {
                $name = $user->name;
                $parts = explode(' ', $name);
                $lastname = array_pop($parts);
                $firstname = implode(' ', $parts);

                try {
                    $user->firstname = $firstname;
                    $user->lastname = $lastname;
                    $user->save();
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    }
}
