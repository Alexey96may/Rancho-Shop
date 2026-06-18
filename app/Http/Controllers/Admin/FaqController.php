<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\AdminFaqResource;
use App\Models\Faq;
use App\Http\Requests\Admin\{FaqRequest, ReorderFaqRequest};
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Traits\Http\Controllers\HandlesSmartPagination;

class FaqController extends Controller
{
    use HandlesSmartPagination;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search']);

        $faqs = Faq::query()
            ->filter($filters)
            ->orderBy('sort_order', 'asc')
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Faq/Index', [
            'faqs' => AdminFaqResource::collection($faqs),
            'filters' => $filters,
            'seo' => $this->seo('Панель управления: Вопросы и Ответы', robots: 'noindex, nofollow')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FaqRequest $request)
    {
        $dto = $request->toDto();
        Faq::create($dto->toArray());

        return redirect()->route('admin.faq.index')->with('success',  "Вопрос успешно добавлен!");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FaqRequest $request, Faq $faq)
    {
        $dto = $request->toDto();
        $faq->update($dto->toArray());

        return redirect()->back()->with('success', "Вопрос #{$faq->id} успешно обновлён!");
    }

    /**
    * Quickly switch publication status
    */
    public function toggle(Faq $faq)
    {
        $faq->update([
            'is_published' => !$faq->is_published 
        ]);

        $message = $faq->is_published ? "Вопрос #{$faq->id} опубликован!" : "Вопрос #{$faq->id} скрыт!";
        return redirect()->back()->with('success', $message);
    }

    /**
    * Bulk order update (if we're doing drag-n-drop)
    */
    public function reorder(ReorderFaqRequest $request)
    {
        $ids = $request->validated()['ids'];
        if (empty($ids)) {
            return redirect()->back();
        }

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
            DB::transaction(fn() => DB::update($query, $params));
            return redirect()->back()->with('success', 'Порядок вопросов обновлён!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Ошибка базы данных: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Faq $faq)
    {   
        $faq->delete();

        return $this->redirectWithFilters($request, 'admin.faq.index', "Вопрос #{$faq->id} удалён!");
    }
}
