<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    // use RefreshDatabase;
    // use WithFaker;

    private array $userData;

    public function __construct()
    {
        parent::__construct();

        $this->userData = [
            'firstname' => 'Amy',
            'lastname' => 'Doe',
            'email' => 'amydoe@packie.io',
            'country' => 'FR',
            'password' => 'password123',
            'tag' => 'amydoe12345',
            'bio' => 'This my awesome biography',
            'photo' => 'https://i.pravatar.cc/150?img=5',
            'link' => null,
            'timezone' => 'Europe/Paris',
        ];
    }

    /** @test */
    public function a_user_can_signup()
    {
        echo __METHOD__ . "\n";

        $response = $this->post('/signup', [
            'firstname' => $this->userData['firstname'],
            'lastname' => $this->userData['lastname'],
            'email' => $this->userData['email'],
            'country' => $this->userData['country'],
            'password' => $this->userData['password'],
        ]);
        echo "POST /signup : " . $response->status()."\n";

        $this->assertDatabaseHas('users', [
            'firstname' => $this->userData['firstname'],
            'lastname' => $this->userData['lastname'],
            'email' => $this->userData['email'],
            'country' => $this->userData['country']
        ]);

        echo "\n";
    }

    /** @test */
    public function a_user_can_update_his_profile()
    {
        echo __METHOD__ . "\n";

        $user = User::where(['email' => $this->userData['email']])->first();

        $response = $this->post('/profile/complete', [
            'id' => $user->id,
            'tag' => $this->userData['tag'],
            'bio' => $this->userData['bio'],
            'photo' => $this->userData['photo'],
            'link' => $this->userData['link'],
            'country' => $this->userData['country'],
            'timezone' => $this->userData['timezone']
        ]);
        echo "POST /profile/complete : " . $response->status()."\n";

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'tag' => $this->userData['tag'],
            'bio' => $this->userData['bio'],
            'photo' => $this->userData['photo'],
            'link' => $this->userData['link'],
            'country' => $this->userData['country'],
            'timezone' => $this->userData['timezone']
        ]);

        echo "\n";
    }

    /** @test */
    public function a_user_can_log_in()
    {
        echo __METHOD__ . "\n";

        $response = $this->post('/login', [
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ]);
        echo "POST /login : " . $response->status()."\n";

        $this->assertAuthenticated();

        echo "\n";
    }

    /** @test */
    public function a_user_can_join_a_community()
    {
        echo __METHOD__ . "\n";

        $community = Community::getIncubateurCommunity();
        $user = User::where(['email' => $this->userData['email']])->first();

        $response = $this->post('/member/join', [
            'communityId' => $community->id,
            'incubateurStart' => true,
            'userId' => $user->id
        ]);
        echo "POST /member/join : " . $response->status()."\n";

        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'role' => 'member',
            'access' => 1,
            'level' => 1
        ]);

        echo "\n";
    }

    public function a_user_can_create_a_community()
    {
        // @todo
    }

    public function a_user_can_leave_a_community()
    {
        // @todo

        /*
        echo __METHOD__ . "\n";

        $community = Community::getIncubateurCommunity();
        $user = User::where(['email' => $this->userData['email']])->first();

        $response = $this->post('/member/leave', [
            'communityId' => $community->id,
            'memberId' => true,
        ]);
        echo "POST /member/leave : " . $response->status()."\n";

        echo "\n";
        */
    }
}
