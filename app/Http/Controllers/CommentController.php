<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class CommentController extends Controller
{
    public function getComments(Request $request)
    {
        try {
            $data = DB::table('comments AS c')
                ->select('c.id', 'c.comment', 'c.created_at', 'users.name as username')
                ->where('c.post_id', '=', $request->post_id)
                ->join('users', 'c.user_id', '=', 'users.id')
                ->orderBy('c.created_at', 'desc')
                ->paginate(
                    $request->size ?? 10,
                    ['c.id', 'c.comment', 'c.created_at', 'users.name'], // Columns to select
                    'page',
                    $request->page ?? 1 // Current page
                );

            return response()->json(['status' => 1, 'message' => 'get', 'data' => $data], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 0, 'message' => $e->getMessage()], 500);
        }
    }
    public function addComment(Request $request)
    {

        DB::beginTransaction();
        try {

            DB::table('comments')->insert([
                'id' => Str::uuid()->toString(),
                'user_id' => Auth::id(),
                'post_id' => $request->post_id,
                'comment' => $request->comment,
                'created_at' => Carbon::now()
            ]);
            DB::commit();
            return response()->json(['status' => 1, 'message' => 'Posted'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 0, 'message' => 'Failed to post'], 500);
        }
    }

    public function deleteComment($id)
    {

        DB::beginTransaction();
        try {

            DB::table('comments')
                ->where('user_id', '=', Auth::id())
                ->where('id', '=', $id)
                ->delete();

            DB::commit();
            return response()->json(['status' => 1, 'message' => 'Posted'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 0, 'message' => 'Failed to post'], 500);
        }
    }
}
