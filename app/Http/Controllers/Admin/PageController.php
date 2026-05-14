<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PageType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminPageResource;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\Rules\Enum;

use App\Services\SanitizeService;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pages = Page::query()
            ->with(['seo', 'media'])
            ->withCount('reviews')
            ->when($request->search, function ($query, $search) {
                $search = mb_strtolower($search, 'UTF-8');
                
                $query->where(function($q) use ($search) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$search}%"]);
                });
            })
            ->latest()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Pages/Index', [
            'pages' => AdminPageResource::collection($pages),
            'filters' => $request->only(['search']),
            'seo' => $this->seo('Панель управления: Страницы', robots: 'noindex, nofollow'),
            'page_types' => PageType::cases()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        return Inertia::render('Admin/Pages/Form', [
            'seo' => $this->seo('Создание новой страницы', robots: 'noindex, nofollow'),
            'page_types' => array_map(fn($case) => [
                'id'    => $case->value,
                'name'  => $case->label(),
                'slug'  => $case->value
            ], PageType::cases()),
            'templates' => [
                ['id' => 'default', 'name' => 'Стандартный'],
                ['id' => 'about', 'name' => 'О компании'],
                ['id' => 'delivery', 'name' => 'Доставка'],
            ],
            'backUrl' => $request->query('back') 
                    ? route('admin.pages.index') . $request->query('back') 
                    : route('admin.pages.index'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'nullable|string',
            'type'      => ['required', new Enum(PageType::class)],
            'is_active' => 'boolean',
            'template' => 'required|string|in:default,delivery,about',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'seo.title'       => 'nullable|string|max:255',
            'seo.description' => 'nullable|string',
            'seo.keywords'    => 'nullable|string',
            'seo.canonical'   => 'nullable|string|url',
            'seo.is_noindex'  => 'boolean',
        ]);

        if (!empty($validated['content'])) {
            $validated['content'] = SanitizeService::cleanHtml($validated['content']);
        }

        $validated['slug'] = Str::slug($validated['slug'] ?? $validated['title']);

        $page = Page::create($validated);

        if ($page->content) {
            $this->moveTemporaryMedia($page->content, $page);
        }

        if ($request->has('seo')) {
            $page->seo()->create($request->input('seo'));
        }

        if ($request->filled('backUrl')) {
            return redirect($request->backUrl)->with('success', "Страница «{$page->title}» созданa!");
        }

        return redirect()->route('admin.pages.index')->with('success', "Страница «{$page->title}» созданa!");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'nullable|string',
            'type'      => ['required', new Enum(PageType::class)],
            'is_active' => 'boolean',
            'template' => 'required|string|in:default,delivery,about',
            'slug' => 'nullable|string|max:255|unique:pages,slug,' . $page->id,
            'seo.title'       => 'nullable|string|max:255',
            'seo.description' => 'nullable|string',
            'seo.keywords'    => 'nullable|string',
            'seo.canonical'   => 'nullable|string|url',
            'seo.is_noindex'  => 'boolean',
        ]);

        if (isset($validated['content'])) {
            $validated['content'] = SanitizeService::cleanHtml($validated['content']);

            $this->sanitizeMedia($validated['content'], $page);
        }
        
        $validated['slug'] = Str::slug($validated['slug'] ?? $validated['title']);

        $page->update($validated);

        if ($request->has('seo')) {
            $page->seo()->updateOrCreate(
                ['seoable_id' => $page->id, 'seoable_type' => Page::class],
                $request->input('seo')
            );
        }

        if ($request->filled('backUrl')) {
            return redirect($request->backUrl)->with('success', "Контент страницы «{$page->title}» обновлён!");
        }

        return redirect()->back()->with('success', 'Контент страницы обновлен!');
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
            'page_types' => array_map(fn($case) => [
                'id'    => $case->value,
                'name'  => $case->label(),
                'slug'  => $case->value
            ], PageType::cases()),

            'templates' => [
                ['id' => 'default', 'name' => 'Стандартный'],
                ['id' => 'about', 'name' => 'О компании'],
                ['id' => 'delivery', 'name' => 'Доставка'],
            ],
            'backUrl' => $request->query('back') 
                ? route('admin.pages.index') . $request->query('back') 
                : route('admin.pages.index'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page)
    {
        if (!$page->isDeletable()) {
            return redirect()->back()->with('error', 'Эту страницу нельзя удалить, так как она является системной.');
        }

        $page->seo()->delete();
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', "Страница $page->title удалена");
    }

    /** 
     * Upload Media files to pages (from text-editor or etc)
    */
    public function uploadMedia(Request $request, Page $page)
    {
        $request->validate([
                'image' => 'required|image|mimes:jpeg,png,webp,gif|max:3072',
            ],
            [
                'image.max' => 'Изображение слишком тяжелое (не более 3 МБ).',
                'image.image' => 'Файл должен быть картинкой.',
            ]);

        $media = $page->addMediaFromRequest('image')->toMediaCollection('content_images');
        $url = $media->getFullUrl();

        return back()->with('last_uploaded_url', $url);
    }

    /** 
     * Upload Unsociated Media files to pages (from text-editor or etc)
    */
    public function uploadTemporaryMedia(Request $request)
    {
        $request->validate([
                'image' => 'required|image|mimes:jpeg,png,webp,gif|max:3072',
            ],
            [
                'image.max' => 'Изображение слишком тяжелое (не более 3 МБ).',
                'image.image' => 'Файл должен быть картинкой.',
            ]);

        $user = Auth::user();
        
        $media = $user->addMediaFromRequest('image')
            ->toMediaCollection('tmp');

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
        $user = Auth::user();
        $temporaryMedia = $user->getMedia('tmp');

        foreach ($temporaryMedia as $media) {
            $url = $media->getUrl();
            
            if (str_contains($content, $url)) {
                $media->move($page, 'content_images');
            }
        }
        
        $user->clearMediaCollectionExcept('tmp', $user->getMedia('tmp')->where('created_at', '>', now()->subDay()));
    }
}
