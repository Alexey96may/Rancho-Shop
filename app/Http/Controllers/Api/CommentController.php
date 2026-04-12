<?php

namespace App\Http\Controllers\Api;

use App\Models\Animal;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;

class CommentController extends Controller
{
    public function index(Animal $animal)
    {
        $comments = $animal->comments()
            ->latest()
            ->paginate(10);

        return CommentResource::collection($comments);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'commentable_id' => ['required', 'integer'],
            'commentable_type' => ['required', 'string'],
        ]);

        $comment = \App\Models\Comment::create($data);

        return new CommentResource($comment);
    }
}