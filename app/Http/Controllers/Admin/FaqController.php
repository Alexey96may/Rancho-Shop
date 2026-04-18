<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use App\Services\SanitizeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $faqs = Faq::query()
            ->when($request->search, function ($query, $search) {
                $query->where('question', 'ilike', "%{$search}%");
            })
            ->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Faq/Index', [
            'faqs' => FaqResource::collection($faqs),
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
            'question'     => 'required|string|max:500',
            'answer'       => 'required|string',
            'is_published' => 'boolean',
            'sort_order'   => 'nullable|integer',
        ]);

        // Очищаем HTML в ответе
        $validated['answer'] = SanitizeService::cleanHtml($validated['answer']);

        // Если порядок не указан, ставим в конец
        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = Faq::max('sort_order') + 1;
        }

        Faq::create($validated);

        return redirect()->route('admin.faq.index')
            ->with('success', 'Вопрос добавлен в базу знаний');
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question'     => 'required|string|max:500',
            'answer'       => 'required|string',
            'is_published' => 'boolean',
            'sort_order'   => 'required|integer',
        ]);

        $validated['answer'] = SanitizeService::cleanHtml($validated['answer']);

        $faq->update($validated);

        return redirect()->back()
            ->with('success', 'Вопрос успешно обновлен');
    }

    /**
    * Quickly switch publication status
    */
    public function toggle(Faq $faq)
    {
        $faq->update(['is_published' => !$faq->is_published]);
        return redirect()->back();
    }

    /**
    * Bulk order update (if we're doing drag-n-drop)
    */
    public function reorder(Request $request)
    {
        $request->validate(['ids' => 'required|array']);

        foreach ($request->ids as $index => $id) {
            Faq::where('id', $id)->update(['sort_order' => $index]);
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq $faq)
    {   
        $faq->delete();
        return redirect()->route('admin.faq.index')
            ->with('success', 'Вопрос удален');
    }
}
