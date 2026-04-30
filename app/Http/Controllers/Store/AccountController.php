<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    public function orders()
    {
        $orders = auth()->user()
            ->orders()
            ->with('items')
            ->latest()
            ->paginate(10);

        return view('store.account.orders', compact('orders'));
    }

    public function show(string $code)
    {
        $order = auth()->user()
            ->orders()
            ->with('items.product')
            ->where('code', $code)
            ->firstOrFail();

        return view('store.account.show', compact('order'));
    }
}
