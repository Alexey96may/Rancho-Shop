<?php

namespace App\Http\Controllers\Profile;

use App\Enums\CommentStatus;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileCommentController extends Controller
{
    public function index(Request $request): Response
    {
        // Оставляем только загрузку истории комментариев текущего юзера
        $comments = $request->user()->comments()
            ->with('commentable') // Чтобы знать, к какому товару/странице был коммент
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Profile/Comments', [
            'comments' => $comments,
        ]);
    }

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        abort_if($comment->user_id !== $request->user()->id, 403);
        
        if ($comment->status === CommentStatus::APPROVED->value) {
            return back()->with('error', 'Нельзя изменить уже одобренный отзыв.');
        }

        $validated = $request->validate([
            'content' => ['required', 'string', 'min:5', 'max:1000'],
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $comment->update([
            ...$validated,
            'status' => CommentStatus::PENDING->value, // На перемодерацию
        ]);

        return back()->with('success', 'Отзыв успешно обновлен и отправлен на модерацию!');
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        abort_if($comment->user_id !== $request->user()->id, 403);

        $comment->delete();

        return back()->with('success', 'Отзыв успешно удален.');
    }
}