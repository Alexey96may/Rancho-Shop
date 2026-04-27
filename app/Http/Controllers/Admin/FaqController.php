<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminFaqResource;
use App\Models\Faq;
use App\Services\SanitizeService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $faqs = Faq::query()
            ->when($request->search, function ($query, $search) {
                $search = mb_strtolower($search, 'UTF-8');
                $query->whereRaw('LOWER(question) LIKE ?', ["%{$search}%"]);
            })
            ->orderBy('sort_order', 'asc')
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Faq/Index', [
            'faqs' => AdminFaqResource::collection($faqs),
            'filters' => $request->only(['search']),
            'seo' => $this->seo('Панель управления: Вопросы и Ответы', robots: 'noindex, nofollow')
        ]);
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

        $validated['answer'] = SanitizeService::cleanHtml($validated['answer']);

        if (!isset($validated['sort_order'])) {
            $validated['sort_order'] = (Faq::max('sort_order') ?? 0) + 1;
        }

        Faq::create($validated);

        return redirect()->route('admin.faq.index')
            ->with('success', "Вопрос добавлен в базу знаний");
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
        $faq->update([
            'is_published' => !$faq->is_published 
        ]);

        return redirect()->back()->with('success', $faq->is_published ? 'Вопрос опубликован' : 'Вопрос скрыт');
    }

    /**
    * Bulk order update (if we're doing drag-n-drop)
    */
    public function reorder(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        $ids = $request->ids;

        if (empty($ids)) return redirect()->back();

        $cases = [];
        $params = [];

        foreach ($ids as $index => $id) {
            $cases[] = "WHEN id = ? THEN ?::integer";
            $params[] = $id;
            $params[] = $index;
        }

        $params = array_merge($params, $ids);
        $idsPlaceholder = implode(',', array_fill(0, count($ids), '?'));

        $query = "
            UPDATE faqs 
            SET sort_order = (CASE " . implode(' ', $cases) . " END) 
            WHERE id IN ($idsPlaceholder)
        ";

        try {
            DB::transaction(function () use ($query, $params) {
                DB::update($query, $params);
            });

            return redirect()->back()->with('success', 'Порядок вопросов обновлен');
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
            }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq $faq)
    {   
        $faq->delete();
        return redirect()->route('admin.faq.index')
            ->with('success', 'Вопрос удален!');
    }
}
