<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\SendMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SiteController extends Controller
{
    public function sendMail(Request $request)
    {
        $subject = "Mail interation test";
        $data  = [
            'name'  => 'Namaraj',
            'body'  => 'This is test message',
        ];
        Mail::to('test@gmail.com')->send(new SendMail($subject, $data));

        return response()->json([
            'status' => 'success',
            'message' => 'Mail sent successfully.',
        ]);
    }

    public function profile()
    {
        $user = Auth::user();
        return response()->json([
            'status'    =>  'ok',
            'data'  =>  $user
        ], JsonResponse::HTTP_OK);
    }

    public function updateProfile(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => ['sometimes', 'current_password'],
            'new_password' => ['sometimes', 'min:6', 'confirmed'],
            'first_name' => ['sometimes', 'string'],
            'last_name' => ['sometimes', 'string'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $id],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    =>  'error',
                'message'   =>  $validator->errors(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $data = $request->only(['first_name', 'last_name', 'email']);
        $user = User::findorfail($id);
        if ($request->filled('new_password')) {
            $data['password'] = bcrypt($request->new_password);
        }

        $user->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully.',
            'user' => $user,
        ], JsonResponse::HTTP_OK);
    }
}
