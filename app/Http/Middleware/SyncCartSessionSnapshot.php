<?php

namespace App\Http\Middleware;

use App\Services\CartService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SyncCartSessionSnapshot
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasSession()) {
            $request->session()->put('cart', $this->cartService->legacySnapshot(
                $request->user(),
                $request->session()->getId(),
                $request->session()->get('pending_guest_cart_session_id')
            ));
        }

        return $next($request);
    }
}
