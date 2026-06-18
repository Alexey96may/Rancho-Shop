<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PageType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminPageResource;
use App\Http\Requests\Admin\{PageSaveRequest, UploadPageMediaRequest};
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\Http\Controllers\HandlesSmartPagination;
use Inertia\Inertia;

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
            ->latest()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Pages/Index', [
            'pages'         => AdminPageResource::collection($pages),
            'filters'       => $filters,
            'seo'           => $this->seo('Панель управления: Страницы', robots: 'noindex, nofollow'),
            'page_types'    => PageType::cases()
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
            'templates'  => $this->getPageTemplates(),
            'backUrl' => $request->query('back') 
                    ? route('admin.pages.index') . $request->query('back') 
                    : route('admin.pages.index'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PageSaveRequest $request)
    {
        $dto = $request->toDto();
        
        $page = Page::create($dto->toPageArray());

        if ($page->content) {
            $this->moveTemporaryMedia($page->content, $page);
        }

        if (!empty($dto->seoData)) {
            $page->seo()->create($dto->seoData);
        }

        return $this->redirectWithFilters($request, 'admin.pages.index', "Страница «{$page->title}» созданa!");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PageSaveRequest $request, Page $page)
    {
        $dto = $request->toDto();

        if ($dto->content !== null) {
            $this->sanitizeMedia($dto->content, $page);
        }

        $page->update($dto->toPageArray());

        if (!empty($dto->seoData)) {
            $page->seo()->updateOrCreate(
                ['seoable_id' => $page->id, 'seoable_type' => Page::class],
                $dto->seoData
            );
        }

        return $this->redirectWithFilters($request, 'admin.pages.index', "Контент страницы «{$page->title}» обновлён!");
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
    public function destroy(Request $request, Page $page)
    {
        if (!$page->isDeletable()) {
            return redirect()->back()->with('error', 'Эту страницу нельзя удалить, так как она является системной.');
        }

        $page->seo()->delete();
        $page->delete();

        return $this->redirectWithFilters($request, 'admin.pages.index', "Страница «{$page->title}» успешно удалена!");
    }

    /** 
     * Upload Media files to pages (from text-editor or etc)
    */
    public function uploadMedia(UploadPageMediaRequest $request, Page $page)
    {
        $media = $page->addMediaFromInput($request, 'image')->toMediaCollection('content_images');

        return back()->with('last_uploaded_url', $media->getFullUrl());
    }

    /** 
     * Upload Unsociated Media files to pages (from text-editor or etc)
    */
    public function uploadTemporaryMedia(UploadPageMediaRequest $request)
    {
        /** @var \App\Models\User|\Spatie\MediaLibrary\HasMedia $user */

        $user = Auth::user();
        $media = $user->addMediaFromInput($request, 'image')->toMediaCollection('tmp');

        return back()->with('last_uploaded_url', $media->getFullUrl());
    }
    
    /**
     * Delete Unusable Media 
     */
    private function sanitizeMedia(string $content, Page $page): void
    {
        $mediaItems = $page->getMedia('content_images');

        foreach ($mediaItems as $media) {
            if (!str_contains($content, $media->getUrl())) {
                $media->delete();
            }
        }
    }

    /** 
     * Move Temporary Media files to the admin/moder tmp
    */
    private function moveTemporaryMedia(string $content, Page $page)
    {
        /** @var \App\Models\User|\Spatie\MediaLibrary\HasMedia $user */
        $user = Auth::user();
        $temporaryMedia = $user->getMedia('tmp');

        foreach ($temporaryMedia as $media) {
            if (str_contains($content, $media->getUrl())) {
                $media->move($page, 'content_images');
            }
        }
        
        $user->clearMediaCollectionExcept('tmp', $user->getMedia('tmp')->where('created_at', '>', now()->subDay()));
    }

    private function getFormattedPageTypes(): array
    {
        return array_map(fn($case) => [
            'id'   => $case->value,
            'name' => $case->label(),
            'slug' => $case->value
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
