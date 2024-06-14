<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
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
}
