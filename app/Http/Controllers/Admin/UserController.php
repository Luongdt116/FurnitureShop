<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'role' => ['nullable', Rule::in(['admin', 'customer'])],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $users = User::query()
            ->withCount('orders')
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->when($filters['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('is_active', $status === 'active'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['customer', 'admin'])],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $user, $validated) {
            $lockedUser = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            $willLoseAdminAccess = $lockedUser->isAdmin()
                && ((int) $validated['is_active'] !== 1 || $validated['role'] !== 'admin');

            if ($lockedUser->is($request->user()) && $willLoseAdminAccess) {
                throw ValidationException::withMessages([
                    'role' => 'Bạn không thể tự hạ quyền hoặc khóa tài khoản quản trị của mình.',
                ]);
            }

            if ($willLoseAdminAccess) {
                $remainingActiveAdmins = User::query()
                    ->where('role', 'admin')
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get()
                    ->reject(fn (User $candidate) => $candidate->is($lockedUser))
                    ->count();

                if ($remainingActiveAdmins === 0) {
                    throw ValidationException::withMessages([
                        'role' => 'Cần giữ lại ít nhất một tài khoản quản trị đang hoạt động.',
                    ]);
                }
            }

            $lockedUser->role = $validated['role'];
            $lockedUser->is_active = (bool) $validated['is_active'];
            $lockedUser->save();
        });

        return back()->with('success', 'Đã cập nhật vai trò và trạng thái tài khoản.');
    }
}
