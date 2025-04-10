<?php

namespace app\Console\Commands\Database;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedAlphaDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:seed-alpha-database-1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if (config('app.env') === 'production') {
            return;
        }

        DB::insert("INSERT INTO `users` (`id`, `firstname`, `lastname`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `token`, `language`) VALUES ('1', 'Kevin', 'hanot', 'h.kevin@loupfute.com', NULL, '$2y$10$4lONiTIARa6e8ylamROUJObAdhG7Ccp6hH8xtWVsGCjvmslfwZC/6', NULL, '2023-12-11 17:47:27', '2025-03-06 14:12:48', 'tKQGWYH5Owm47v083cPhKzsnW49QvNjc', 'fr');");
        DB::insert("INSERT INTO `community_members` (`id`, `user_id`, `firstname`, `lastname`, `tag`, `content`, `photo`, `link`, `country`, `last_activity`, `created_at`, `updated_at`, `timezone`, `stripe_customer_id`) VALUES ('1', '1', 'Kevin', 'hanot', 'Kevinhanot', 'Tech CEO, innovator, and thrill-seeker. Passionate about software, pushing boundaries, and embracing life\'s intense experiences. Always evolving.', 'https://wolfeo.s3.eu-west-1.amazonaws.com/2023/12/12/kevinla-min__150x149-1702378182.png', 'https://alphapacky.com/Incubateur', 'FR', '2025-03-06 08:40:58', '2023-12-30 21:04:58', '2025-03-06 08:40:58', NULL, 'cus_RgKkP8KVMp5HlR');");
        DB::insert("INSERT INTO `community` (`id`, `member_id`, `name`, `privacy`, `owner_show`, `summary_description`, `description`, `summary_photo`, `logo`, `favicon`, `url`, `last_sent_notification`, `auto_post_approbation`, `tab_classrooms`, `tab_calendar`, `tab_leaderboard`, `created_at`, `updated_at`, `status`) VALUES ('1', '1', 'Incubateur (OLD Community)', 'private', '1', '', 'Tout le monde peut voir qui fait partie du groupe et ce qui est publié. Le contenu est indexé par les moteurs de recherche.', 'https://wolfeo.s3.eu-west-1.amazonaws.com/uploads/6746026629/Logo-incubateur.png', 'https://wolfeo.s3.eu-west-1.amazonaws.com/uploads/6746026629/I-fav.png', 'https://wolfeo.s3.eu-west-1.amazonaws.com/uploads/6746026629/I-fav.png', 'incubateur', '2025-02-24 17:34:11', '1', '1', '1', '1', '2024-01-03 15:58:00', '2025-02-25 01:34:13', '1');");
        DB::insert("INSERT INTO `community_member_settings` (`id`, `community_id`, `member_id`, `popular_interval`, `unread_interval`, `likes`, `admin_announce`, `event_reminder`, `created_at`, `updated_at`) VALUES ('1', '1', '1', 'weekly', 'hourly', '1', '1', '1', '2023-12-20 15:34:24', '2024-09-15 13:16:37');");
        DB::insert("INSERT INTO `community_has_members` (`id`, `community_id`, `member_id`, `role`, `access`, `created_at`, `updated_at`, `monthly_point`, `weekly_point`, `point`, `level`) VALUES ('36', '1', '1', 'owner', '1', '2024-11-30 02:38:19', '2025-01-16 16:20:37', '0', '0', '0', '1');");
        DB::insert("INSERT INTO `community_posts` (`id`, `community_id`, `classroom_lesson_id`, `member_id`, `title`, `content`, `path`, `pinned`, `category_id`, `likes`, `visibility`, `disable_comment`, `created_at`, `updated_at`) VALUES ('4', '1', NULL, '1', 'Guide de bienvenue', '<p>Vous &ecirc;tes dans la communaut&eacute; officielle de la R&eacute;volution Cr&eacute;ateurs. C\'est votre espace d\'&eacute;change avec les autres membres ainsi que mon &eacute;quipe.</p>\n<p>Ce sera notre moyen de communication n&deg;1 pour vous envoyer les nouveaut&eacute;s ainsi qu\'apporter un support marketing.</p>', 'guide-de-bienvenue-5606', '1', '0', '8,3,1,0,0', '1', '0', '2023-12-06 00:27:55', '2024-09-10 18:54:02');");
    }
}
