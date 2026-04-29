<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Persist a store cart as a pending order and return the order code.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.id'       => 'required|exists:products,id',
            'items.*.name'     => 'required|string|max:255',
            'items.*.price'    => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_type'=> 'required|in:unit,weight',
            'comment'          => 'nullable|string|max:1000',
        ]);

        $code = 'ORD-' . strtoupper(Str::random(6));

        $order = Order::create([
            'code'    => $code,
            'user_id' => auth()->id(),
            'status'  => 'pending',
            'comment' => $validated['comment'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $item['id'],
                'product_name' => $item['name'],
                'price'        => $item['price'],
                'quantity'     => $item['quantity'],
                'unit_type'    => $item['unit_type'],
            ]);
        }

        return response()->json(['code' => $code]);
    }

    /**
     * Admin: list all orders.
     */
    public function index()
    {
        $orders = Order::with('user')
            ->withCount('items')
            ->latest()
            ->paginate(25);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Admin: order detail.
     */
    public function show(string $code)
    {
        $order = Order::with('items.product', 'user')
            ->where('code', $code)
            ->firstOrFail();

        return view('admin.orders.show', compact('order'));
    }

    /**
     * Admin: cancel a pending order.
     */
    public function cancel(string $code)
    {
        $order = Order::where('code', $code)->firstOrFail();

        if ($order->status !== 'pending') {
            return back()->with('error', 'Solo se pueden cancelar pedidos pendientes.');
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->route('admin.orders.index')
            ->with('success', "Pedido {$code} cancelado correctamente.");
    }
}
