<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCommentStatusRequest;
use App\Http\Resources\Admin\AdminCommentResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use App\Enums\UserRole;
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
        $filters = $request->only(['type', 'status']);

        $comments = Comment::query()
            ->with(['user', 'commentable' => fn($q) => $q->withTrashed()])
            ->withTrashControl($request, $filters)
            ->filter($filters)
            ->latest()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Comments/Index', [
            'comments'  => AdminCommentResource::collection($comments),
            'filters'   => $filters,
            'stats'     => Comment::getStats(),
            'statuses'  => collect(CommentStatus::cases())->map(fn($s) => [
                'value' => $s->value,
                'label' => $s->label()
            ]),
            'seo'       => $this->seo('Панель управления: Комментарии', robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentStatusRequest $request, Comment $comment)
    {
        $comment->update($request->validated());

        return back()->with('success', 'Статус комментария изменен на: «' . $comment->status->label() . '»');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $comment = Comment::withTrashed()->findOrFail($id);

        if ($comment->trashed()) {
            Gate::authorize('forceDelete', $comment);

            $comment->forceDelete();

            return back()->with('success', 'Комментарий окончательно удалён из базы данных!');
        }

        $comment->delete();
        return back()->with('success', "Комментарий помечен как удалённый!");
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(Comment $comment): RedirectResponse
    {
        Gate::authorize('restore', $comment);

        $comment->restore();

        return redirect()->back()->with('success', "Комментарий успешно восстановлен!");
    }
}
