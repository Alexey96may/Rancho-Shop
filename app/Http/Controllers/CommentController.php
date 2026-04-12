<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'commentable_id' => ['required', 'integer'],
            'commentable_type' => ['required', 'string'],
            'content' => ['required', 'string'],
        ]);

        Comment::create([
            ...$data,
            'user_id' => Auth::id(),
            'author_name' => Auth::user()?->name ?? 'Гость',
        ]);

        return back()->with('success', 'Комментарий добавлен');
    }
}
