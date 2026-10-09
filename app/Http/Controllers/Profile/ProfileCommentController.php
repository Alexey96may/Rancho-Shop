<?php

namespace App\Http\Controllers\Profile;

use App\Enums\CommentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileCommentController extends Controller
{
    public function index(Request $request): Response
    {
        $comments = $request->user()->comments()
            ->with('commentable')
            ->latest()
            ->paginate(setting('reviews_per_page', 12))
            ->withQueryString();

        return Inertia::render('Profile/Comments', [
            'comments' => CommentResource::collection($comments),
            'seo' => $this->seo('Мои Комментарии', robots: 'noindex, nofollow'),
        ]);
    }

    public function update(Request $request, Comment $comment): RedirectResponse
    {
        // 1. Проверка прав владения
        if ($comment->user_id !== $request->user()->id) {
            abort(403);
        }

        // 2. Корректное сравнение Enum (учитываем $casts в модели)
        $isApproved = $comment->status === CommentStatus::APPROVED
            || $comment->status === CommentStatus::APPROVED->value;

        if ($isApproved) {
            return back()->with('error', 'Нельзя изменить уже одобренный отзыв.');
        }

        // 3. Синхронизированная валидация
        $validated = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:1000'],
            'rating' => ['nullable', 'numeric', 'between:1,5'],
        ]);

        // 4. Явное обновление полей
        $comment->update([
            'content' => $validated['content'],
            'rating' => $validated['rating'] ?? null,
            'status' => CommentStatus::PENDING,
        ]);

        return back()->with('success', 'Отзыв успешно обновлен и отправлен на модерацию!');
    }

    public function destroy(Request $request, Comment $comment): RedirectResponse
    {
        if ($comment->user_id !== $request->user()->id) {
            abort(403);
        }

        $comment->delete();

        return back()->with('success', 'Отзыв успешно удален!');
    }
}
