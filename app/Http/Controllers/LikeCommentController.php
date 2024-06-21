<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LikeCommentController extends Controller
{
    public function comment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content'   =>  ['required'],
            'post_id'   => ['required', 'exists:posts,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    =>  'error',
                'errors'    =>  $validator->errors(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $data = $request->all();
        $data['user_id'] = Auth::user()->id;

        $comment = Comment::create($data);

        return response()->json([
            'status'    =>  'ok',
            'message'   =>  'Comment created successfully',
            'data'  =>  $comment
        ], JsonResponse::HTTP_CREATED);
    }

    public function likeUnlike(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id'   => ['required', 'exists:posts,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'    =>  'error',
                'errors'    =>  $validator->errors(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $userId = Auth::user()->id;
        $data = $request->all();
        $data['user_id'] =  $userId;

        $like = Like::where('user_id', $userId)->where('post_id', $request->post_id)->First();
        if ($like) {
            $like->delete();
            return response()->json([
                'status'    =>  'ok',
                'message'    =>  'Post unliked successfully'
            ], JsonResponse::HTTP_OK);
        } else {
            $like = Like::Create($data);
            return response()->json([
                'status'    =>  'ok',
                'message'    =>  'Post liked successfully'
            ], JsonResponse::HTTP_OK);
        }
    }
}
