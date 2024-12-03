<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{


    public function getProfileList(Request $request)
    {
        $search = '%' . $request->search ?? '' . '%';
        $query = DB::table('users')->select('users.id','users.username', 'users.name', 'users.profile_picture', 'users.profile_picture_path');
        if (!empty($search)) {
            $query->where(function ($where) use ($search) {
                $where->where('users.username', 'LIKE', $search)->orWhere('users.name', 'LIKE', $search);
            });
        }
        $data = $query->paginate($request->size ?? 10, ['*'], 'page', $request->page ?? 1);
        return response()->json(['status' => 1, 'message' => 'get', 'data' => $data], 200);
    }

    public function getProfile(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string'
        ]);

        $userId = $request->user_id;

        $user = DB::table('users')
            ->select('id', 'username', 'name', 'bio', 'profile_picture', 'profile_picture_path')
            ->where('id', $userId)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 0,
                'message' => 'User not found',
                'data' => null
            ], 404);
        }

        $data = [
            'informations' => $user,
            'posts' => DB::table('posts')
                ->where('user_id', $userId)
                ->count(),
            'followers' => DB::table('follows')
                ->where('following_id', $userId)
                ->count(),
            'followings' => DB::table('follows')
                ->where('followed_id', $userId)
                ->count(),
        ];

        return response()->json([
            'status' => 1,
            'message' => 'Data retrieved successfully',
            'data' => $data
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $id = Auth::id();
        $validate = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'username' => 'required|unique:users,username,' . $id,
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validate->fails()) {
            return response()->json($validate->messages(), 400);
        }

        DB::beginTransaction();

        try {
            $user = DB::table('users')->where('id', $id)->first();

            if (!$user) {
                DB::rollBack();
                return response()->json(['message' => 'User not found'], 404);
            }

            $data = [
                'name' => request('name'),
                'email' => request('email'),
                'updated_at' => now()
            ];

            if ($request->hasFile('profile_picture')) {
                $profile_picture = $request->file('profile_picture')->store('profile_pictures', 'public');
                $file_name = basename($profile_picture);
                $data['profile_picture'] = $file_name;
                $data['profile_picture_path'] =  url('storage/profile_pictures/' . $file_name);
            }
            if (request('bio')) {
                $data['bio'] = $request->bio;
            }

            if (request('password')) {
                $data['password'] = Hash::make(request('password'));
            }
            if (request('username')) {
                $data['username'] = $request->username;
            }

            $updated = DB::table('users')
                ->where('id', $id)
                ->update($data);

            if ($updated) {
                DB::commit();
                return response()->json(['message' => 'User updated successfully']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update user', 'error' => $e->getMessage()], 500);
        }
    }
}
