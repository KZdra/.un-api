<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;



class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getOwnUser() {
        $user = auth()->user();
        if ($user) {
            return response()->json(['status'=> 1,'message'=>'data_get','data'=>$user],200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }

    }
    
    public function register(Request $request)
    {
        $validate = Validator::make(request()->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'username'=>'required|unique:users',
            'password' => 'required|confirmed',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        if ($validate->fails()) {
            return response()->json($validate->messages(), 400);
        }
        if ($request->hasFile('profile_picture')) {
            $profile_picture = $request->file('profile_picture')->store('profile_pictures','public');
            $file_name = basename($profile_picture);
        } else {
            $file_name = null;
        }

        try {
            DB::table('users')->insert([
                'id' => Str::uuid()->toString(),
                'username'=>request('username'),
                'name'=> request('name'),
                'email'=> request('email'),
                'profile_picture' => $file_name,
                'profile_picture_path' => $file_name !== null ? url('storage/profile_pictures/' . $file_name) : null,
                'password'=> Hash::make(request('password')),
                'created_at'=>now()
            ]);
            
            return response()->json(['message' => 'Registrasi Sukses']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal: ' . $e->getMessage() ,'s'=>$e], 500);
        }
    }
    

    
  
    
//     public function deleteUser($id){
//         $user = User::find($id);
//         if (!$user) {
//             return response()->json(['message' => 'User not found'], 404);
//         }
//         if ($user->delete()) {
//        return response()->json(['message'=> 'USER DELETED!']);
//     } else {
//         return response()->json(['message' => 'Failed to Delete User'], 500);

//     }
// }
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user= auth()->user();
        return $this->respondWithToken($token,$user);
    }
    
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function me()
    // {
    //     return response()->json(auth()->user());
    // }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $user= auth()->user();
        return $this->respondWithToken(auth()->refresh(),$user);
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token ,$user)
    {
        return response()->json([
            'access_token' => $token,
            'user'=> $user,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 360
        ]);
    }
}
