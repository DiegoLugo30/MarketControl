<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ $sale->id }} — {{ config('store.name', 'Tienda') }}</title>
    <style>
        /* ── Base ───────────────────────────────────────────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.3;
            color: #111;
            background: #fff;
            padding: 16px;
            max-width: 480px;
            margin: 0 auto;
        }

        /* ── Header ─────────────────────────────────────────────── */
        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #555;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .store-name {
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .receipt-meta {
            font-size: 11px;
            color: #444;
            margin-top: 3px;
        }

        /* ── Customer / comment block ───────────────────────────── */
        .customer-block {
            border: 1px dashed #aaa;
            border-radius: 3px;
            padding: 5px 8px;
            margin-bottom: 10px;
            font-size: 11px;
            line-height: 1.5;
        }
        .customer-block strong { color: #333; }

        /* ── Items table ────────────────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .items-table th {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #333;
            padding: 3px 2px;
            text-align: left;
        }
        .items-table th.right,
        .items-table td.right { text-align: right; }
        .items-table th.center,
        .items-table td.center { text-align: center; }

        .items-table td {
            padding: 4px 2px;
            vertical-align: top;
            border-bottom: 1px dashed #ddd;
            font-size: 11.5px;
        }
        .item-name {
            max-width: 150px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            font-weight: 600;
        }
        .item-badge {
            font-size: 9px;
            color: #666;
            font-style: italic;
        }

        /* ── Totals ─────────────────────────────────────────────── */
        .totals {
            border-top: 2px dashed #555;
            padding-top: 6px;
            margin-top: 4px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
            padding: 1.5px 0;
        }
        .totals-row.grand {
            font-size: 15px;
            font-weight: bold;
            border-top: 1px solid #333;
            margin-top: 4px;
            padding-top: 4px;
        }
        .totals-row.discount { color: #c00; }

        /* ── Footer ─────────────────────────────────────────────── */
        .receipt-footer {
            border-top: 2px dashed #555;
            margin-top: 10px;
            padding-top: 8px;
            text-align: center;
            font-size: 11px;
            color: #444;
            line-height: 1.6;
        }
        .payment-badge {
            display: inline-block;
            border: 1px solid #333;
            border-radius: 3px;
            padding: 2px 8px;
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 4px;
        }

        /* ── No-print controls ──────────────────────────────────── */
        .no-print {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 16px;
        }
        .btn {
            padding: 7px 18px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print  { background: #1d4ed8; color: #fff; }
        .btn-print:hover  { background: #1e40af; }
        .btn-close  { background: #e5e7eb; color: #374151; }
        .btn-close:hover  { background: #d1d5db; }

        /* ── Print media ────────────────────────────────────────── */
        @media print {
            body {
                padding: 0;
                font-size: 11px;
                max-width: 100%;
            }
            .no-print { display: none !important; }
            @page {
                margin: 10mm;
                size: A4 portrait;
            }
        }
    </style>
</head>
<body>

{{-- ── Print / Close controls (hidden on print) ────────────────── --}}
<div class="no-print">
    <button onclick="window.print()" class="btn btn-print">
        &#128424; Imprimir
    </button>
    <a href="javascript:window.close()" class="btn btn-close">
        &#10005; Cerrar
    </a>
</div>

{{-- ── Header ───────────────────────────────────────────────────── --}}
<div class="receipt-header">
    <div class="store-name">{{ config('store.name', 'Tienda') }}</div>
    <div class="receipt-meta">
        Ticket #{{ $sale->id }} &nbsp;·&nbsp;
        {{ $sale->created_at->format('d/m/Y H:i') }}
    </div>
</div>

{{-- ── Customer / order info (if from online order) ──────────────── --}}
@if($sale->order_code)
<div class="customer-block">
    <strong>Pedido:</strong> {{ $sale->order_code }}<br>
    <strong>Cliente:</strong> {{ $sale->customer_name ?? 'Invitado' }}
    @if($sale->customer_email)
        &nbsp;·&nbsp; {{ $sale->customer_email }}
    @endif
    @if($sale->order_comment)
        <br><strong>Nota:</strong> <em>{{ $sale->order_comment }}</em>
    @endif
</div>
@endif

{{-- ── Items ────────────────────────────────────────────────────── --}}
@php
    $paymentLabels = [
        'efectivo'      => 'Efectivo',
        'debito'        => 'Débito',
        'transferencia' => 'Transferencia',
        'cuenta_dni'    => 'Cuenta DNI',
        'rappi'         => 'Rappi',
    ];

    function fmtWeight(float $kg): string {
        if ($kg < 1) {
            $g = (int) round($kg * 1000);
            return $g . ' g';
        }
        // Remove trailing zeros: 1.500 → 1.5, 2.000 → 2
        return rtrim(rtrim(number_format($kg, 3, '.', ''), '0'), '.') . ' kg';
    }

    function fmtMoney(float $amount): string {
        // Drop decimals when they are .00
        return '$' . (fmod($amount, 1) === 0.0
            ? number_format($amount, 0, '.', '')
            : number_format($amount, 2, '.', ''));
    }
@endphp

<table class="items-table">
    <thead>
        <tr>
            <th style="width:42%">Producto</th>
            <th class="center" style="width:18%">Cant.</th>
            <th class="right" style="width:18%">P. Unit</th>
            <th class="right" style="width:22%">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sale->items as $item)
            @php
                $isWeighted    = $item->isWeighted();
                $subtotal      = (float) $item->subtotal;
                $afterDiscount = (float) $item->total_with_discount;
                $hasDiscount   = $item->item_discount > 0;
            @endphp
            <tr>
                <td>
                    <div class="item-name" title="{{ $item->getDisplayName() }}">
                        {{ $item->getDisplayName() }}
                    </div>
                    @if($isWeighted)
                        <div class="item-badge">a granel</div>
                    @elseif($item->is_custom)
                        <div class="item-badge">manual</div>
                    @endif
                </td>
                <td class="center">
                    @if($isWeighted)
                        {{ fmtWeight((float) $item->weight) }}
                    @else
                        {{ $item->quantity }}
                    @endif
                </td>
                <td class="right">
                    @if($isWeighted)
                        {{ fmtMoney((float) $item->price) }}<span style="font-size:9px">/kg</span>
                    @else
                        {{ fmtMoney((float) $item->price) }}
                    @endif
                </td>
                <td class="right">
                    @if($hasDiscount)
                        <span style="text-decoration:line-through;color:#999;font-size:10px">
                            {{ fmtMoney($subtotal) }}
                        </span><br>
                        {{ fmtMoney($afterDiscount) }}
                    @else
                        {{ fmtMoney($subtotal) }}
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- ── Totals ───────────────────────────────────────────────────── --}}
<div class="totals">
    @php
        $itemDiscountsTotal = (float) $sale->calculateItemDiscounts();
        $generalDiscount    = (float) $sale->discount_amount;
        $subtotalDisplay    = (float) $sale->calculateSubtotal();
    @endphp

    @if($itemDiscountsTotal > 0 || $generalDiscount > 0)
        <div class="totals-row">
            <span>Subtotal</span>
            <span>{{ fmtMoney($subtotalDisplay) }}</span>
        </div>
    @endif

    @if($itemDiscountsTotal > 0)
        <div class="totals-row discount">
            <span>Desc. items</span>
            <span>-{{ fmtMoney($itemDiscountsTotal) }}</span>
        </div>
    @endif

    @if($generalDiscount > 0)
        <div class="totals-row discount">
            <span>
                Desc. general
                @if($sale->discount_description)
                    <span style="font-size:10px;color:#888">({{ $sale->discount_description }})</span>
                @endif
            </span>
            <span>-{{ fmtMoney($generalDiscount) }}</span>
        </div>
    @endif

    <div class="totals-row grand">
        <span>TOTAL</span>
        <span>{{ fmtMoney((float) $sale->total) }}</span>
    </div>
</div>

{{-- ── Footer ───────────────────────────────────────────────────── --}}
<div class="receipt-footer">
    <div class="payment-badge">
        {{ $paymentLabels[$sale->payment_method] ?? ucfirst($sale->payment_method) }}
    </div>
    <br>
    ¡Gracias por su compra!
    <br>
    {{ config('store.name', 'Tienda') }}
</div>

</body>
</html>
