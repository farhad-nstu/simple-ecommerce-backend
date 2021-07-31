<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Hash;

class AuthApiController extends Controller
{
    public function register(Request $request) {
    	$user = new User();
    	$user->name = $request->name;
    	$user->email = $request->email;
    	$user->password = Hash::make($request->password);
    	$user->save();
        // return response()->json(["user" => $user]);
    	return $user;
    }

    public function login(Request $request) {
        $user = User::where('email', $request->email)->first();

        if(Hash::check($request->password, $user->password)) {
            // return response()->json(["user" => $user]);
            return $user;
        } else {
            return ['error' => 'Email or Password not matched!'];
        }
    }

    public function user()
    {
    	$users = User::all();
    	return $users;
    }
}
