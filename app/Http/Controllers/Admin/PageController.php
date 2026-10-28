<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Pages\CreatePageAction;
use App\Actions\Admin\Pages\DeletePageAction;
use App\Actions\Admin\Pages\UpdatePageAction;
use App\Enums\PageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageSaveRequest;
use App\Http\Requests\Admin\UploadPageMediaRequest;
use App\Http\Resources\Admin\AdminPageResource;
use App\Models\Page;
use App\Models\User;
use App\Traits\Http\Controllers\HandlesSmartPagination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use RuntimeException;
use Spatie\MediaLibrary\HasMedia;

class PageController extends Controller
{
    use HandlesSmartPagination;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search']);

        $pages = Page::query()
            ->with(['seo', 'media'])
            ->withCount('reviews')
            ->filter($filters)
            ->latest('id')
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Pages/Index', [
            'pages' => AdminPageResource::collection($pages),
            'filters' => $filters,
            'seo' => $this->seo('Панель управления: Страницы', robots: 'noindex, nofollow'),
            'page_types' => PageType::cases(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return Inertia::render('Admin/Pages/Form', [
            'seo' => $this->seo('Создание новой страницы', robots: 'noindex, nofollow'),
            'page_types' => $this->getFormattedPageTypes(),
            'templates' => $this->getPageTemplates(),
            'backUrl' => $request->query('back')
                    ? route('admin.pages.index') . $request->query('back')
                    : route('admin.pages.index'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PageSaveRequest $request, CreatePageAction $action)
    {
        $page = $action->handle($request->toDto());

        return $this->redirectWithFilters(
            $request, 'admin.pages.index',
            "Страница «{$page->title}» создана!"
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PageSaveRequest $request, Page $page, UpdatePageAction $action)
    {
        $action->handle($page, $request->toDto($page->id));

        return $this->redirectWithFilters(
            $request, 'admin.pages.index',
            "Контент страницы «{$page->title}» обновлён!"
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Page $page)
    {
        $page->load(['seo', 'media'])->loadCount('reviews');

        return Inertia::render('Admin/Pages/Form', [
            'page' => new AdminPageResource($page),
            'seo' => $this->seo("Редактирование: {$page->title}", robots: 'noindex, nofollow'),
            'page_types' => $this->getFormattedPageTypes(),
            'templates' => $this->getPageTemplates(),
            'backUrl' => $request->query('back')
                ? route('admin.pages.index') . $request->query('back')
                : route('admin.pages.index'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Page $page, DeletePageAction $action)
    {
        $title = $page->title;

        try {
            $action->handle($page);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return $this->redirectWithFilters(
            $request, 'admin.pages.index',
            "Страница «{$title}» успешно удалена!"
        );
    }

    /**
     * Upload Media files to pages (from text-editor or etc)
     */
    public function uploadMedia(UploadPageMediaRequest $request, Page $page)
    {
        $media = $page->addMediaFromRequest('image')->toMediaCollection('content_images');

        return back()->with('last_uploaded_url', $media->getFullUrl());
    }

    /**
     * Upload Unsociated Media files to pages (from text-editor or etc)
     */
    public function uploadTemporaryMedia(UploadPageMediaRequest $request)
    {
        /** @var User|HasMedia $user */
        $user = Auth::user();
        $media = $user->addMediaFromRequest('image')->toMediaCollection('tmp');

        return back()->with('last_uploaded_url', $media->getFullUrl());
    }

    private function getFormattedPageTypes(): array
    {
        return array_map(fn ($case) => [
            'id' => $case->value,
            'name' => $case->label(),
            'slug' => $case->value,
        ], PageType::cases());
    }

    private function getPageTemplates(): array
    {
        return [
            ['id' => 'default', 'name' => 'Стандартный'],
            ['id' => 'about', 'name' => 'О компании'],
            ['id' => 'delivery', 'name' => 'Доставка'],
        ];
    }
}
