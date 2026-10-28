<?php

namespace App\Http\Controllers;

use App\Actions\Comments\CreateCommentAction;
use App\Actions\Comments\EnsureCommentableAction;
use App\Enums\CommentableType;
use App\Enums\CommentStatus;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $sort = in_array($request->query('sort'), ['created_at', 'rating'])
            ? $request->query('sort')
            : 'created_at';

        $direction = strtolower($request->query('direction')) === 'asc' ? 'asc' : 'desc';

        $comments = Comment::query()
            ->published()
            ->general()
            ->orderBy($sort, $direction)
            ->orderBy('id', 'desc')
            ->paginate(setting('reviews_per_page', 12))
            ->withQueryString();

        return Inertia::render('Reviews/index', [
            'comments' => CommentResource::collection($comments),
            'filters' => [
                'sort' => $sort,
                'direction' => $direction,
            ],
            'seo' => $this->seo('Комментарии - Молочная долина'),
        ]);
    }

    public function store(
        StoreCommentRequest $request,
        EnsureCommentableAction $ensureCommentable,
        CreateCommentAction $createComment,
    ) {
        $user = $request->user();
        $data = $request->validated();

        $type = isset($data['commentable_type'])
            ? CommentableType::from($data['commentable_type'])
            : null;

        $ensureCommentable->handle($type, $data['commentable_id'] ?? null);

        $comment = $createComment->handle($data, $user);

        $message = $comment->status === CommentStatus::PENDING
            ? 'Спасибо за отзыв! Он появится на сайте после проверки модератором.'
            : 'Комментарий успешно опубликован.';

        return back()->with('success', $message);
    }
}
