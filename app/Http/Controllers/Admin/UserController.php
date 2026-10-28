<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserSaveRequest;
use App\Http\Resources\Admin\AdminUserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'role']);

        $users = User::query()
            ->withCount(['orders', 'comments'])
            ->filter($filters)
            ->latest()
            ->paginate(setting('admin_per_page', 10))
            ->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => AdminUserResource::collection($users),
            'filters' => $filters,
            'roles' => $this->getRolesOptions(),
            'seo' => $this->seo('Панель управления: Пользователи', robots: 'noindex, nofollow'),
        ]);
    }

    public function store(UserSaveRequest $request)
    {
        $dto = $request->toDto();

        // The password is hashed automatically inside the model thanks to the Mutator
        User::create($dto->toArray());

        return back()->with('success', 'Пользователь создан!');
    }

    public function update(UserSaveRequest $request, User $user)
    {
        $dto = $request->toDto();

        $user->update($dto->toArray());

        return back()->with('success', 'Данные пользователя обновлены!');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return back()->with('success', 'Пользователь удалён!');
    }

    private function getRolesOptions(): array
    {
        return collect(UserRole::cases())->map(fn ($role) => [
            'value' => $role->value,
            'label' => $role->label(),
            'color' => $role->color(),
        ])->toArray();
    }
}
