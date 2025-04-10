<?php declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * @param string|null $search
     * @param int $page
     * @return LengthAwarePaginator
     */
    public function getPaginatedUsers(string $search = null, int $page = 0): LengthAwarePaginator
    {
        $query = User::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('users.firstname', 'LIKE', "%{$search}%")
                    ->orWhere('users.lastname', 'LIKE', "%{$search}%")
                    ->orWhere('users.tag', 'LIKE', "%{$search}%")
                    ->orWhere('users.email', 'LIKE', "%{$search}%");
            });
        }

        $query->select('users.*', 'communityHasMembers.communityIds');
        $query->leftJoin('community_members', 'users.id', '=', 'community_members.user_id');
        $query->leftJoin(DB::raw("(select user_id, group_concat(community_id SEPARATOR ',') as communityIds from `community_members` where `community_members`.`role` = 'owner' group by user_id) communityHasMembers"),
                function ($join) {
                    $join->on('communityHasMembers.user_id', '=', 'users.id');
                }
            );

        $query->where('users.id', '!=', 1);

        return $query
            ->distinct()
            ->orderBy('users.created_at', 'asc')
            ->paginate(10, ['*'], 'page', $page);
    }

    /**
     * @param User $user
     * @param array $request
     * @return void
     */
    public function updateUser(User $user, array $request): void
    {
        $tag = $request['tag'] ?? null;

        $existingTag = User::where(['tag' => $tag])
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingTag || !$tag) {
            $firstname = $request['firstname'] ?? '';
            $lastname = $request['lastname'] ?? '';
            $name = trim($firstname . ' ' . $lastname);
            $tag = str_replace(' ', '', $name) . mt_rand(10000, 99999);
        }

        try {
            if (isset($request['firstname']) && $request['firstname']) {
                $user->firstname = $request['firstname'];
            }
            if (isset($request['lastname']) && $request['lastname']) {
                $user->lastname = $request['lastname'];
            }
            $user->tag = $tag;
            $user->last_activity = date('Y-m-d H:i:s');
            $user->bio = $request['content'] ?? '';
            $user->photo = $request['photo'] ?? '';
            $user->link = $request['link'] ?? '';
            $user->timezone = $request['timezone'] ?? '';
            $user->save();
        } catch (\Exception $e) {
            LoggerService::logException(__METHOD__, __LINE__, $e->getMessage());
        }
    }
}
