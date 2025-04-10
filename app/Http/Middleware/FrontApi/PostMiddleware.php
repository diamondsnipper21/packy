<?php

namespace App\Http\Middleware\FrontApi;

use Closure;
use Illuminate\Http\Request;
use App\Models\CommunityPost;
use Symfony\Component\HttpFoundation\Response;

class PostMiddleware
{
    /**
     * Check if member has permission for community.
     *
     * @param  Request  $request
     * @param  Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $communityId = $request->communityId ?? 0;
        $postId = $request->postId ?? '';

        $post = CommunityPost::find($postId);
        if (!$post || (int) $post->community_id !== (int) $communityId) {
            return response()->json([
                'success' => false,
                'message' => __('Post not found')
            ], 404);
        }

        $request->merge(['post' => $post]);
        
        return $next($request);
    }
}
