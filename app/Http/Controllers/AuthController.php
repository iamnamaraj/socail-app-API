<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\fpwMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'    =>  ['required'],
            'last_name'     =>  ['required'],
            'email'         =>  ['required', 'unique:users,email'],
            'password'      =>  ['required', 'min:6', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    =>  'error',
                'message'   =>  $validator->errors(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $data = $request->all();
        if (!empty($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        $user = User::create($data);

        return response()->json([
            'status'    =>  'ok',
            'message' => 'User registered successfully',
            'data'  =>  $user,
        ], JsonResponse::HTTP_CREATED);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation errors',
                'errors' => $validator->errors(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $credentials = $request->only(['password', 'email']);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Revoke all existing tokens for the user if any exist
            if ($user->tokens()->exists()) {
                $user->tokens()->delete();
            }

            $token = $user->createToken('AccessToken')->accessToken;

            return response()->json([
                'status' => 'success',
                'access_token' => $token,
                'user' => $user,
            ]);
        }

        // Check if user exists with the given email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User does not exist.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        // If user exists but password is incorrect
        return response()->json([
            'status' => 'error',
            'message' => 'The provided credentials are incorrect.',
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function logout()
    {
        $user = Auth::user();

        // Revoke all existing tokens for the user if any exist
        if ($user->tokens()->exists()) {
            $user->tokens()->delete();
        }

        return response()->json([
            'status'    =>  'ok',
            'message'   =>  'User logout successfully'
        ], JsonResponse::HTTP_OK);
    }

    public function forgetPassword(Request $request)
    {
        $validator = validator::make($request->all(), [
            'email' =>  ['required', 'email']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    =>  'error',
                'message'   =>  $validator->errors(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $code = rand(1111, 9999);
        $user->remember_token = $code;
        $user->save();

        $subject = "Reset password";
        $body  = [
            'name'  => $user->first_name,
            'code'  =>  $code,
        ];
        Mail::to($user->email)->send(new fpwMail($subject, $body));

        return response()->json([
            'status'    =>  'ok',
            'message'   =>  'Password reset code has been sent to youe email.'
        ], JsonResponse::HTTP_OK);
    }

    public function resetPassword(Request $request)
    {
        $validator = validator::make($request->all(), [
            'email' =>  ['required', 'email', 'exists:users,email'],
            'new_password'  =>  ['required', 'min:6', 'confirmed'],
            'token'    =>  ['required', 'integer'],

        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    =>  'error',
                'message'   =>  $validator->errors(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $data = $request->all();

        $user = User::where('email', $data['email'])
            ->where('remember_token', $data['token'])
            ->First();

        if (!$user) {
            return response()->json([
                'status'    =>  'error',
                'message'   =>  'User not found',
            ], JsonResponse::HTTP_BAD_REQUEST);
        } else {
            $user->remember_token = null;
            $user->password = bcrypt($data['new_password']);
            $user->save();

            return response()->json([
                'status'    =>  'ok',
                'message'   =>  'Password changed successfully.',
            ], JsonResponse::HTTP_OK);
        }
    }
}
