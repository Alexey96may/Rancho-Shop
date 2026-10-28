<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\Products\CreateProductAction;
use App\Actions\Admin\Products\DeleteProductAction;
use App\Actions\Admin\Products\RestoreProductAction;
use App\Actions\Admin\Products\UpdateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\Admin\AdminProductResource;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CommentResource;
use App\Models\Animal;
use App\Models\Category;
use App\Models\Product;
use App\Traits\Http\Controllers\HandlesSmartPagination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use RuntimeException;

class ProductController extends Controller
{
    use HandlesSmartPagination;

    public function index(Request $request)
    {
        $filters = $request->only(['search', 'category', 'animal']);

        $products = Product::query()
            ->with(['category', 'variants.unit'])
            ->withCount(['variants', 'comments'])
            ->withAvg('comments', 'rating')
            ->withTrashControl($request, $filters)
            ->filter($filters)
            ->sortAdmin()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => AdminProductResource::collection($products),
            'filters' => $filters,
            'categories' => CategoryResource::collection(
                Category::forProducts()->orderBy('name')->get()
            ),
            'animals' => $this->getAnimalOptions(),
            'seo' => $this->seo('Панель управления: Продукты', 'Просмотр продуктов', robots: 'noindex, nofollow'),
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Admin/Products/Form', array_merge([
            'seo' => $this->seo('Создание продукта', robots: 'noindex, nofollow'),
            'backUrl' => $this->backUrl($request),
        ], $this->getFormOptions()));
    }

    public function store(StoreProductRequest $request, CreateProductAction $action)
    {
        $product = $action->handle($request->toDto(), $request);

        return $this->redirectWithFilters(
            $request,
            'admin.products.index',
            "Товар «{$product->name}» создан!"
        );
    }

    public function edit(Request $request, Product $product)
    {
        $product->load(['variants.unit', 'category', 'media', 'animals', 'seo'])
            ->loadCount(['variants', 'comments'])
            ->loadAvg('comments', 'rating');

        return Inertia::render('Admin/Products/Form', array_merge([
            'product' => AdminProductResource::make($product),
            'seo' => $this->seo('Редактирование: ' . $product->name, robots: 'noindex, nofollow'),
            'comments' => CommentResource::collection(
                $product->comments()->with('user')->latest()->paginate(10)
            ),
            'backUrl' => $this->backUrl($request),
        ], $this->getFormOptions()));
    }

    public function update(UpdateProductRequest $request, Product $product, UpdateProductAction $action)
    {
        $action->handle($product, $request->toDto($product->id), $request);

        return $this->redirectWithFilters(
            $request,
            'admin.products.index',
            "Товар «{$product->name}» обновлён!"
        );
    }

    public function destroy(Request $request, int $id, DeleteProductAction $action): RedirectResponse
    {
        $product = Product::withTrashed()->findOrFail($id);
        $name = $product->name;
        $wasTrashed = $product->trashed();

        try {
            $action->handle($product);
        } catch (RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $message = $wasTrashed
            ? "Товар «{$name}» окончательно удалён!"
            : "Товар «{$name}» помечен как удалённый!";

        return $this->redirectWithFilters($request, 'admin.products.index', $message);
    }

    public function restore(Product $product, RestoreProductAction $action): RedirectResponse
    {
        $action->handle($product);

        return back()->with('success', "Товар «{$product->name}» успешно восстановлен!");
    }

    private function getFormOptions(): array
    {
        return [
            'categories' => CategoryResource::collection(
                Category::forProducts()->orderBy('name')->get()
            ),
            'animals' => $this->getAnimalOptions(),
        ];
    }

    private function getAnimalOptions(): array
    {
        return Animal::query()
            ->where('is_active', true)
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(fn ($animal) => [
                'id' => $animal->id,
                'name' => $animal->name,
            ])
            ->all();
    }

    private function backUrl(Request $request): string
    {
        return $request->query('back')
            ? route('admin.products.index') . $request->query('back')
            : route('admin.products.index');
    }
}
