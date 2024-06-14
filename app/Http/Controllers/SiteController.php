<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

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
}
