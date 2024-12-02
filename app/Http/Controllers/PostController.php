<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostController extends Controller
{

    public function POSTS($size = 10, $page = 1,$user_id = ''){
        // $search = '%'.$search.'%';
    //todo get with the comments?
        $query = DB::table('posts')
        ->select(
            'posts.id',
            'posts.user_id',
            'posts.post_picture',
            'posts.post_picture_path',
            'posts.title',
            'posts.content',
            'users.name'
        )
        ->join('users', 'posts.user_id', '=', 'users.id');
    
        if(!empty($user_id)){
            $query->where('posts.user_id','=',$user_id);
        }
            // if (!empty($search)) {
            //         $query->where(function ($where) use ($search) {
            //         $where->where('p.nama_nasabah', 'ILIKE', $search)
            //             ->orWhere('p.no_permohonan', 'ILIKE', $search)
            //             ->orWhere('jk.name', 'ILIKE', $search)
            //             ->orWhere('m.name', 'ILIKE', $search);
            //     });
            // }
    
            $data = $query->orderBy('posts.created_at', 'desc')
                        ->paginate($size, ['*'], 'page', $page);
        return $data;
    }
    public function addPost(Request $request){
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
                'created_at'=> Carbon::now()
            ]);
        
      
        DB::commit();
        return response()->json(['status' => 1, 'message' => 'Post Created'], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => 0, 'message' => 'Failed to create post'], 500);
    }
}

    public function getPosts(Request $request){
        $data = $this->POSTS(
            $request->size ?? 10,
            $request->page ?? 1,
       );
       return response()->json(['status'=>1,'message'=>'get','data'=>$data], 200);
    }

    public function getPostsByUserId(Request $request){
        $data = $this->POSTS(
            $request->size ?? 10,
            $request->page ?? 1,
            $request->user_id ?? ''
       );
       return response()->json(['status'=>1,'message'=>'get','data'=>$data], 200);
    }
// Comments Sections
    public function addComment(Request $request){
        
            DB::beginTransaction();
            try {
                
                    DB::table('comments')->insert([
                        'id' => Str::uuid()->toString(),
                        'user_id' => Auth::id(),
                        'post_id' => $request->post_id,
                        'comment'=> $request->comment,
                    ]);
                    DB::commit();
                    return response()->json(['status' => 1, 'message' => 'Posted'], 201);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return response()->json(['status' => 0, 'message' => 'Failed to post'], 500);
                }
}

    public function getComments(Request $request){
        try {
        $data = DB::table('comments')->select('comments.id','comments.user_id','comments.comment','comments.created_at','users.username')
            ->join('users','comments.user_id','=','users.id')
            ->where('comments.post_id','=',$request->post_id)
            ->orderBy('comments.created_at', 'desc')
            ->paginate($request->size ?? 10, ['*'], 'page', $request->page ?? 1);
            return response()->json(['status'=>1,'message'=>'get','data'=>$data], 200);
        } catch (\Exception $e) {
            return response()->json(['status'=>0,'message'=>$e->getMessage()], 500);
        }
    }
}