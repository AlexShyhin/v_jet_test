<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller {

    public function register(Request $request) : JsonResponse {

        $validator = Validator::make($request->all(), [
            'first_name'    => 'required|string|min:3|max:50',
            'last_name'     => 'required|string|min:3|max:50',
            'email'         => 'required|email|unique:users,email',
            'phone'         => 'required|string|unique:users,phone',
            'password'      => 'required|string|confirmed'
        ], [
            'first_name.required'   => 'First name is required.',
            'first_name.min'        => 'First name is too short.',
            'first_name.max'        => 'First name is too long.',

            'last_name.required'    => 'Last name is required.',
            'last_name.min'         => 'Last name is too short.',
            'last_name.max'         => 'Last name is too long.',

            'email.required'        => 'Email is required',
            'email.email'           => 'Email format is wrong.',
            'email.unique'          => 'Email is already registered',

            'phone.required'        => 'Phone number is required.',
            'phone.unique'          => 'Phone number is registered',

            'password.required'     => 'Password is required',
            'password.confirmed'    => 'Password confirmation failed',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag()->getMessages();

            return response()->json(['status' => 'fail', 'error_messages' => $messages]);
        }
        $user = User::createUser($request->all());

        return response()->json(['status' => 'success', 'user' => $user->toArray()]);
    }

    public function signIn(Request $request) : JsonResponse  {
        $validator = Validator::make($request->all(), [
            'email'         => 'required|email|exists:users,email',
            'password'      => 'required|string'
        ], [
            'email.required'        => 'Email is required',
            'email.exists'          => 'Email is not registered',

            'phone.required'        => 'Phone number is required.',
            'phone.unique'          => 'Phone number is registered',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag()->getMessages();

            return response()->json(['status' => 'fail', 'error_messages' => $messages]);
        }

        $token = User::autorize($request->all());

        if ($token) {
            return response()->json(['status' => 'success', 'auth_token' => $token]);
        }

        return response()->json(['status' => 'fail', 'error_messages' => 'Wrong password']);
    }

    public function recoverPassword(Request $request) : JsonResponse  {
        $validator = Validator::make($request->all(), [
            'email'         => 'required|email|exists:users,email',
        ], [
            'email.required'        => 'Email is required',
            'email.exists'          => 'Email is not registered',
        ]);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag()->getMessages();

            return response()->json(['status' => 'fail', 'error_messages' => $messages]);
        }

        $user = User::where('email', $request->email)->first();

        $password = mb_substr(md5(uniqid(rand(), true)), -8);

        $user->changePassword($password);

        Mail::send('resetPassword', ['new_password' => $password, 'user_name' => $user->fullName], function ($message) use ($user) {
            $message->to($user->email, $user->fullName)->subject('Reset password');
            $message->from(env('MAIL_USERNAME'), env('APP_NAME'));
        });

        return response()->json(['status' => 'success', 'message' => 'New Password was swnd to your email']);
    }
}
