<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Token;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{

    public function validateToken(Request $request) {
        $token = Token::where('value', '=', $request->cookie('token'))->first();

        return $token->user;
    }

    public function store(StoreUserRequest $request) {
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
    }

    public function login (Request $request) {

        $user = User::where('email', '=', $request->email)->first();
        
        if (Hash::check($request->password, $user->password)){
            foreach ($user->tokens as $token){
                $token->delete();
            }

            $token = Token::create([
                'value' => Str::random(10),
                'user_id' => $user->id,
            ]);

            return setcookie('token', $token->value, time() + (86400));
        }
    }

    public function logout(Request $request) {
        Token::where('value','=', $request->cookie('token'))->delete();
        return setcookie('token', '', time() + -1);
    }
}
