<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class LikeController extends Controller
{
    // Like Sections
    public function addLike(Request $request)
    {
        $isLiked = DB::table('likes')
            ->where('user_id', Auth::id())
            ->where('post_id', $request->post_id)
            ->exists();

        if ($isLiked) {
            DB::beginTransaction();
            try {
                DB::table('likes')
                    ->where('user_id', Auth::id())
                    ->where('post_id', $request->post_id)
                    ->delete();
                DB::commit();
                return response()->json(['status' => 1, 'message' => 'unliked!', 'data' => 0], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 0, 'message' => $e->getMessage()], 500);
            }
        }
        if (!$isLiked) {
            DB::beginTransaction();
            try {
                DB::table('likes')->insert([
                    'id' => Str::uuid()->toString(),
                    'user_id' => Auth::id(),
                    'post_id' => $request->post_id,
                    'created_at' => now(),
                ]);
                DB::commit();
                return response()->json(['status' => 1, 'message' => 'liked!', 'data' => 1], 201);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 0, 'message' => $e->getMessage()], 500);
            }
        }
    }
}
