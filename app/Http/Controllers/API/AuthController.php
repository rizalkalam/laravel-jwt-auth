<?php

namespace App\Http\Controllers\API;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // validasi
        $data = $request->only('name', 'email', 'password');
        $validator = Validator::make($data, [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6'
        ]);

        // respon valid atau tidak valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $user = User::create([
            'name' => $request->name,
        	'email' => $request->email,
        	'password' => bcrypt($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil dibuat',
            'data' => $user
        ], 200);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // validasi
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        // respon valid atau tidak valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        try {
            if(! $token = JWTAuth::attempt($credentials)){
                return response()->json([
                	'success' => false,
                	'message' => 'Login kredensial tidak valid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
                return response()->json([
                    'success' => false,
                	'message' => 'Could not create token.',
                ], 500);
        }

        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        // validasi
        $validator = Validator::make($request->only('token'),[
            'token' => 'required'
        ]);

        // respon valid atau tidak valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'success' => true,
                'message' => 'User sudah logout'
            ]);
        } catch (JWTExeption $exception) {
            return response()->json([
                'success' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], 500);
        }
    }

    public function get_user(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
 
        $user = JWTAuth::authenticate($request->token); //->jwtauth::authenticate
 
        return response()->json(['user' => $user]);
    }
}
