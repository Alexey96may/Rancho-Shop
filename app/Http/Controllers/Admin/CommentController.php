<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $comments = Comment::query()
        ->with(['user', 'commentable'])
        ->when($request->type, function ($query, $type) {
            return $query->where('commentable_type', $type);
        })
        ->when($request->status, function ($query, $status) {
            if ($status === 'published') return $query->where('is_published', true);
            if ($status === 'draft') return $query->where('is_published', false);
        })
        ->latest()
        ->paginate(setting('comments_per_page', 8))
        ->withQueryString();

    return Inertia::render('Admin/Comments/Index', [
            'comments' => CommentResource::collection($comments),
            'filters'  => $request->only(['type']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $comment->update($request->only('is_published'));
        return back()->with('success', $comment->is_published ? 'Отзыв опубликован' : 'Отзыв скрыт');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        
        return back()->with('warning', 'Отзыв удален');
    }
}
