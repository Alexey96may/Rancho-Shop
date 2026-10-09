<?php

namespace App\Http\Controllers;

use App\Enums\CommentableType;
use App\Enums\CommentStatus;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
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
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'commentable_id' => ['nullable', 'integer'],
            'commentable_type' => ['nullable', 'string', new Enum(CommentableType::class)],
            'content' => ['required', 'string', 'min:1'],
            'rating' => ['nullable', 'numeric', 'between:1,5'],
            'guest_name' => [Rule::requiredIf(!$user), 'nullable', 'string', 'max:50'],
        ]);

        $status = $user->is_admin ?? false
            ? CommentStatus::APPROVED
            : CommentStatus::PENDING;

        Comment::create([
            'commentable_id' => $data['commentable_id'],
            'commentable_type' => $data['commentable_type'],
            'content' => $data['content'],
            'rating' => $data['rating'] ?? null,
            'user_id' => $user?->id,
            'guest_name' => $user ? null : $data['guest_name'],
            'status' => $status,
        ]);

        $message = $status === CommentStatus::PENDING
            ? 'Спасибо за отзыв! Он появится на сайте после проверки модератором.'
            : 'Комментарий успешно опубликован.';

        return back()->with('success', $message);
    }
}
