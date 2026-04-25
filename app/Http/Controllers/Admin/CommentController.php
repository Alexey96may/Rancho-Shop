<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommentStatus;
use Illuminate\Validation\Rules\Enum;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminCommentResource;
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
                return $query->where('status', $status);
            })
            ->orderBy(function ($query) {
                $query->selectRaw("CASE 
                    WHEN status = ? THEN 1 
                    WHEN status = ? THEN 2 
                    WHEN status = ? THEN 3 
                    ELSE 4 END", [
                        CommentStatus::PENDING->value,
                        CommentStatus::APPROVED->value,
                        CommentStatus::HIDDEN->value,
                ]);
            })
            ->latest()
            ->paginate(setting('comments_per_page', 8))
            ->withQueryString();

        $stats = [
            'avg_rating' => round(Comment::where('status', CommentStatus::APPROVED)->avg('rating') ?? 0, 1),
            'total_count' => Comment::count(),
            'pending_count' => Comment::where('status', CommentStatus::PENDING)->count(),
        ];

        return Inertia::render('Admin/Comments/Index', [
            'comments' => AdminCommentResource::collection($comments),
            'filters'  => $request->only(['type', 'status']),
            'stats'    => $stats,
            'statuses' => collect(CommentStatus::cases())->map(fn($s) => [
                'value' => $s->value,
                'label' => $s->label()
            ]),
            'seo' => $this->seo('Панель управления: Комментарии', robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        $validated = $request->validate([
            'status' => ['required', new Enum(CommentStatus::class)],
        ]);

        $comment->update($validated);

        return back()->with('success', 'Статус комментария изменен на: ' . $comment->status->label());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();
        
        return back()->with('warning', 'Отзыв перемещен в корзину');
    }
}
