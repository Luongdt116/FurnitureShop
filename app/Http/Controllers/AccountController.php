<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function profile(): View
    {
        return view('storefront.account', ['tab' => 'profile']);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'regex:/^(03|05|07|08|09)[0-9]{8}$/'],
        ], [
            'email.unique' => 'Email này đã được sử dụng.',
            'phone.regex' => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 03, 05, 07, 08 hoặc 09.',
        ]);

        $user->fill($validated)->save();

        return to_route('account.profile')->with('success', 'Thông tin hồ sơ đã được cập nhật.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.current_password' => 'Mật khẩu hiện tại không chính xác.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
        ]);

        $request->user()->forceFill(['password' => $validated['password']])->save();
        $request->session()->regenerate();

        return to_route('account.profile')->with('success', 'Mật khẩu đã được cập nhật.');
    }

    public function addresses(Request $request): View
    {
        $addresses = $request->user()->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        return view('storefront.account', ['tab' => 'addresses', 'addresses' => $addresses]);
    }

    public function storeAddress(Request $request): RedirectResponse
    {
        $validated = $this->validateAddress($request);
        $user = $request->user();

        DB::transaction(function () use ($user, $validated): void {
            $lockedUser = $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $hasAddresses = $lockedUser->addresses()->exists();
            $makeDefault = (bool) ($validated['is_default'] ?? false) || !$hasAddresses;

            if ($makeDefault) {
                $lockedUser->addresses()->update(['is_default' => false]);
            }

            unset($validated['is_default']);
            $lockedUser->addresses()->create([...$validated, 'is_default' => $makeDefault]);
        }, 3);

        return to_route('account.addresses')->with('success', 'Địa chỉ giao hàng đã được thêm.');
    }

    public function updateAddress(Request $request, int $address): RedirectResponse
    {
        $validated = $this->validateAddress($request, $address);
        $user = $request->user();

        DB::transaction(function () use ($user, $address, $validated): void {
            $lockedUser = $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $userAddress = $lockedUser->addresses()->whereKey($address)->lockForUpdate()->firstOrFail();
            $makeDefault = (bool) ($validated['is_default'] ?? false);

            if ($makeDefault) {
                $lockedUser->addresses()->whereKeyNot($userAddress->id)->update(['is_default' => false]);
            } elseif ($userAddress->is_default) {
                $replacementId = $lockedUser->addresses()->whereKeyNot($userAddress->id)->orderByDesc('created_at')->value('id');
                if ($replacementId) {
                    $lockedUser->addresses()->whereKey($replacementId)->update(['is_default' => true]);
                }
            }

            unset($validated['is_default']);
            $userAddress->fill([...$validated, 'is_default' => $makeDefault])->save();
        }, 3);

        return to_route('account.addresses')->with('success', 'Địa chỉ giao hàng đã được cập nhật.');
    }

    public function setDefaultAddress(Request $request, int $address): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $address): void {
            $lockedUser = $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $userAddress = $lockedUser->addresses()->whereKey($address)->lockForUpdate()->firstOrFail();
            $lockedUser->addresses()->update(['is_default' => false]);
            $userAddress->update(['is_default' => true]);
        }, 3);

        return to_route('account.addresses')->with('success', 'Đã đặt địa chỉ giao hàng mặc định.');
    }

    public function destroyAddress(Request $request, int $address): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $address): void {
            $lockedUser = $user->newQuery()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $userAddress = $lockedUser->addresses()->whereKey($address)->lockForUpdate()->firstOrFail();
            $wasDefault = $userAddress->is_default;
            $userAddress->delete();

            if ($wasDefault) {
                $replacement = $lockedUser->addresses()->orderByDesc('created_at')->first();
                $replacement?->update(['is_default' => true]);
            }
        }, 3);

        return to_route('account.addresses')->with('success', 'Địa chỉ giao hàng đã được xóa.');
    }

    private function validateAddress(Request $request, ?int $addressId = null): array
    {
        $input = $addressId === null ? $request->all() : $request->input("addresses.{$addressId}", []);

        return Validator::make($input, [
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'regex:/^(03|05|07|08|09)[0-9]{8}$/'],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'ward' => ['required', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'max:500'],
            'is_default' => ['sometimes', 'boolean'],
        ], [
            'phone.regex' => 'Số điện thoại phải gồm 10 chữ số và bắt đầu bằng 03, 05, 07, 08 hoặc 09.',
        ])->validate();
    }
}
