<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PageType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminPageResource;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
    public function create()
    {
        return Inertia::render('Admin/Pages/Create', [
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
            ]
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

            //todo
            'seo.title'       => 'nullable|string|max:255',
            'seo.description' => 'nullable|string',
            'seo.keywords'    => 'nullable|string',
        ]);

        if (!empty($validated['content'])) {
            $validated['content'] = SanitizeService::cleanHtml($validated['content']);
        }

        $validated['slug'] = Str::slug($validated['slug'] ?? $validated['title']);

        $page = Page::create($validated);

        if ($request->has('seo')) {
            $page->seo()->create($request->input('seo'));
        }

        return redirect()->route('admin.pages.index')->with('success', 'Страница создана!');
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
            
            //todo
            'seo.title'       => 'nullable|string|max:255',
            'seo.description' => 'nullable|string',
            'seo.keywords'    => 'nullable|string',
        ]);

        if (isset($validated['content'])) {
            $validated['content'] = SanitizeService::cleanHtml($validated['content']);
        }

        $validated['slug'] = Str::slug($validated['slug'] ?? $validated['title']);

        $page->update($validated);

        if ($request->has('seo')) {
            $page->seo()->updateOrCreate(
                ['seoable_id' => $page->id, 'seoable_type' => Page::class],
                $request->input('seo')
            );
        }

        return redirect()->back()->with('success', 'Контент страницы обновлен!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $page)
    {
        $page->load(['seo', 'media'])->loadCount('reviews');

        return Inertia::render('Admin/Pages/Edit', [
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
            ]
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
}
