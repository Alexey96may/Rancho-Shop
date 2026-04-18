<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

use App\Services\SanitizeService;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pages = Page::query()
            // Загружаем SEO и считаем отзывы, как того требует ресурс
            ->with(['seo', 'media'])
            ->withCount('reviews')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'ilike', "%{$search}%"); // ilike для Postgres (регистронезависимо)
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Pages/Index', [
            'pages' => PageResource::collection($pages),
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'nullable|string',
            'type'      => 'required|string',
            'is_active' => 'boolean',
            'seo.title'       => 'nullable|string|max:255',
            'seo.description' => 'nullable|string',
            'seo.keywords'    => 'nullable|string',
        ]);

        // Чистим контент через сервис перед созданием
        if (!empty($validated['content'])) {
            $validated['content'] = SanitizeService::cleanHtml($validated['content']);
        }

        $validated['slug'] = Str::slug($validated['title']);
        $page = Page::create($validated);

        if ($request->has('seo')) {
            $page->seo()->create($request->input('seo'));
        }

        return redirect()->route('admin.pages.index')->with('success', 'Страница создана');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'nullable|string',
            'type'      => 'required|string',
            'is_active' => 'boolean',
            'seo.title'       => 'nullable|string|max:255',
            'seo.description' => 'nullable|string',
            'seo.keywords'    => 'nullable|string',
        ]);

        // Чистим контент через сервис перед обновлением
        if (isset($validated['content'])) {
            $validated['content'] = SanitizeService::cleanHtml($validated['content']);
        }

        if ($page->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $page->update($validated);

        if ($request->has('seo')) {
            $page->seo()->updateOrCreate(
                ['seoable_id' => $page->id, 'seoable_type' => Page::class],
                $request->input('seo')
            );
        }

        return redirect()->back()->with('success', 'Контент страницы обновлен');
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page)
    {
        $page->seo()->delete();
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Страница удалена');
    }
}
