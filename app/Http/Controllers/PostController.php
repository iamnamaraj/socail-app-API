<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = Auth::user()->id;
        $posts = Post::with('user:id,first_name,last_name', 'likes.user:id,first_name,last_name', 'comments.user:id,first_name,last_name')
            ->withCount(['likes', 'comments'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(10);

        if ($posts->isNotEmpty()) {
            return response()->json([
                'status'    =>  'ok',
                'data'  =>  $posts,
            ], JsonResponse::HTTP_OK);
        }
        return response()->json([
            'status'    =>  'ok',
            'message'   =>  'you have not posted anything.',
            'data'  =>  [],
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'caption'   =>  ['sometimes', 'nullable', 'string'],
            'image'     =>  ['sometimes', 'nullable', 'image', 'mimes:png,jpg,jpeg'],
            'visibility' =>  ['sometimes', Rule::in(['public', 'private'])],
        ]);

        // Custom validation rule to check if either caption or image is present
        $validator->after(function ($validator) use ($request) {
            if (!$request->caption && !$request->file('image')) {
                $validator->errors()->add('caption', 'Either caption or image is required.');
                $validator->errors()->add('image', 'Either caption or image is required.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status'    =>  'error',
                'errors' => $validator->errors(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $data = $request->all();

        if ($request->hasFile('image')) {
            // Store the new image
            $image = $request->file('image');
            $fileName = Str::random(10) . '_' . $image->getClientOriginalName();
            $filePath = $image->storeAs('public/posts', $fileName);
            $imagePath = str_replace('public/', 'storage/', $filePath);

            // Update $data with new image path
            $data['image'] = $imagePath;
        }
        $data['user_id'] = Auth::user()->id;
        $post = Post::create($data);

        return response()->json([
            'status'    =>  'ok',
            'message'   =>  'Post created successfully',
            'data'  =>  $post,
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function updatePost(Request $request, $id)
    {
        $post = Post::where('user_id', Auth::user()->id)->where('id', $id)->FirstorFail();

        $validator = Validator::make($request->all(), [
            'caption'   => ['sometimes'],
            'image'     => ['sometimes', 'mimes:png,jpg,jpeg'],
            'visibility' => ['sometimes', Rule::in(['public', 'private'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'error',
                'errors' => $validator->errors(),
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        $data = $request->all();
        if ($request->hasFile('image')) {
            // Check if the post has an existing image and delete it
            if (!empty($post->image)) {
                $existingImagePath = str_replace('storage/', 'public/', $post->image);
                if (Storage::exists($existingImagePath)) {
                    Storage::delete($existingImagePath);
                }
            }

            // Store the new image
            $image = $request->file('image');
            $fileName = Str::random(10) . '_' . $image->getClientOriginalName();
            $filePath = $image->storeAs('public/posts', $fileName);
            $imagePath = str_replace('public/', 'storage/', $filePath);

            $data['image'] = $imagePath;
        }
        $post->update($data);

        return response()->json([
            'status'  => 'success',
            'message' => 'Post updated successfully',
            'data'    => $post,
        ], JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::where('user_id', Auth::user()->id)->where('id', $id)->FirstorFail();

        if (!empty($post->image)) {
            $existingImagePath = str_replace('storage/', 'public/', $post->image);
            if (Storage::exists($existingImagePath)) {
                Storage::delete($existingImagePath);
            }
        }

        $post->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Post deleted successfully',
        ], JsonResponse::HTTP_OK);
    }

    public function publicPosts()
    {
        $userId = Auth::user()->id;
        $posts = Post::with('user:id,first_name,last_name', 'likes.user:id,first_name,last_name', 'comments.user:id,first_name,last_name')
            ->withCount(['likes', 'comments'])
            ->where('user_id', $userId)
            ->where('visibility', 'public')
            ->latest()
            ->paginate(10);

        if ($posts->isNotEmpty()) {
            return response()->json([
                'status'    =>  'ok',
                'data'  =>  $posts,
            ], JsonResponse::HTTP_OK);
        }
        return response()->json([
            'status'    =>  'ok',
            'message'   =>  'you have not posted publicly anything.',
            'data'  =>  [],
        ], JsonResponse::HTTP_OK);
    }
}
