<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AiMessageResource;
use App\Models\AiMessage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiMessageController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'role' => $request->string('role')->toString(),
            'session' => $request->string('session')->toString(),
        ];

        $messages = AiMessage::query()
            ->when($filters['role'], fn ($q) => $q->where('role', $filters['role']))
            ->when($filters['session'], fn ($q) => $q->where('session_id', $filters['session']))
            ->latest('id')
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        $stats = [
            'total' => AiMessage::count(),
            'user_messages' => AiMessage::where('role', 'user')->count(),
            'assistant_messages' => AiMessage::where('role', 'assistant')->count(),
            'sessions' => AiMessage::distinct('session_id')->count('session_id'),
        ];

        return Inertia::render('Admin/AiMessages/Index', [
            'messages' => AiMessageResource::collection($messages),
            'filters' => $filters,
            'stats' => $stats,
            'statuses' => [
                ['value' => 'user', 'label' => 'Пользователь'],
                ['value' => 'assistant', 'label' => 'Ассистент'],
            ],
        ]);
    }

    public function destroy(AiMessage $aiMessage)
    {
        $aiMessage->delete();

        return back()->with('success', 'Сообщение удалено');
    }
}
