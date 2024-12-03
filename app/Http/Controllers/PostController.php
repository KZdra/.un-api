<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostController extends Controller
{

    public function POSTS($size = 10, $page = 1, $user_id = '')
    {
        $query = DB::table('posts')
            ->select(
                'posts.id',
                'posts.user_id',
                'posts.post_picture',
                'posts.post_picture_path',
                'posts.title',
                'posts.content',
                'posts.created_at',
                'users.name',
                DB::raw('(SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) as like_count'),
                DB::raw('(SELECT JSON_ARRAYAGG(users.name) FROM likes 
                      JOIN users ON likes.user_id = users.id 
                      WHERE likes.post_id = posts.id) as liked_by'),
                DB::raw('(SELECT JSON_ARRAYAGG(JSON_OBJECT("id", comments.id, "user_id", comments.user_id, "content", comments.comment, "created_at", comments.created_at, "user_name", comment_users.name)) 
                      FROM comments 
                      JOIN users as comment_users ON comments.user_id = comment_users.id 
                      WHERE comments.post_id = posts.id 
                      ORDER BY comments.created_at DESC 
                      LIMIT 2) as recent_comments')
            )
            ->join('users', 'posts.user_id', '=', 'users.id');

        if (!empty($user_id)) {
            $query->where('posts.user_id', '=', $user_id);
        }

        $data = $query->orderBy('posts.created_at', 'desc')
            ->paginate($size, ['*'], 'page', $page);

        return $data;
    }

    public function addPost(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['status' => 0, 'message' => 'User not authenticated'], 401);
        }

        if ($request->hasFile('post_picture')) {
            $post_picture = $request->file('post_picture')->store('posts_images', 'public');
            $file_name = basename($post_picture);
        } else {
            $file_name = null;
        }

        DB::beginTransaction();
        try {

            DB::table('posts')->insert([
                'id' => Str::uuid()->toString(),
                'user_id' => Auth::id(),
                'post_picture' => $file_name,
                'post_picture_path' => $file_name !== null ? url('storage/posts_images/' . $file_name) : null,
                'title' => $request->title,
                'content' => $request->content,
                'created_at' => Carbon::now()
            ]);


            DB::commit();
            return response()->json(['status' => 1, 'message' => 'Post Created'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 0, 'message' => 'Failed to create post'], 500);
        }
    }
    public function deletePost($id)
    {
        if (!Auth::check()) {
            return response()->json(['status' => 0, 'message' => 'User not authenticated'], 401);
        }
        DB::beginTransaction();
        try {
            DB::table('posts')
                ->where('user_id', '=', Auth::id())
                ->where('id', '=', $id)
                ->delete();
            DB::commit();
            return response()->json(['status' => 1, 'message' => 'Post Deleted'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 0, 'message' => 'Failed to Delete post'], 500);
        }
    }

    public function getPosts(Request $request)
    {
        $data = $this->POSTS(
            $request->size ?? 10,
            $request->page ?? 1,
        );
        return response()->json(['status' => 1, 'message' => 'get', 'data' => $data], 200);
    }

    public function getPostsByUserId(Request $request)
    {
        $data = $this->POSTS(
            $request->size ?? 10,
            $request->page ?? 1,
            $request->user_id ?? ''
        );
        return response()->json(['status' => 1, 'message' => 'get', 'data' => $data], 200);
    }

}
