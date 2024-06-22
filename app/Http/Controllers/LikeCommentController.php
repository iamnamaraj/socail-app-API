<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
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

    public function likeUnlike($id)
    {
        $userId = Auth::user()->id;
        $data['user_id'] =  $userId;

        $post = Post::FindorFail($id);

        $data = [
            'post_id' => $post->id,
            'user_id'   =>  $userId,
        ];

        $like = Like::where('user_id', $userId)->where('post_id', $id)->First();
        if ($like) {
            $like->delete();
            return response()->json([
                'status'    =>  'ok',
                'message'    =>  'Post unliked successfully'
            ], JsonResponse::HTTP_NO_CONTENT);
        } else {
            $like = Like::Create($data);
            $like->load('user');
            return response()->json([
                'status'    =>  'ok',
                'message'    =>  'Post liked successfully',
                'data'  =>  $like
            ], JsonResponse::HTTP_CREATED);
        }
    }
}
