<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\User;

class AuthController extends Controller
{
  public function store(Request $request)
  {
    $this->validate($request, [
        'name'=> 'required',
        'email'=> 'required|email',
        'password'=> 'required|min:5'
    ]);

    $name = $request->input('name');
    $email = $request->input('email');
    $password = $request->input('password');

    $user = new User([
        'name'=> $name,
        'email'=> $email,
        'password'=> bcrypt($password)
    ]);

    if($user->save()) {
        $user->signin =   [
            'href'=> 'api/v1/user/signin',
            'method'=> 'POST',
            'params'=> 'email, password'
        ];
        $response = [
            'msg'=> 'User created',
            'user'=> $user
        ];

        return response()->json($response, 201);
    } 

    $response = ['msg' => 'An error occurred' ];

    return  response()->json($response, 404);
  }

  public function sigin(Request $request)
  {

    $this->validate($request, [
        'email'=> 'required|email',
        'password'=> 'required'
    ]);

    $email = $request->input('email');
    $password = $request->input('password');

    return "Sign In";
  }

}
