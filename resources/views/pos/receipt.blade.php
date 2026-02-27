<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=auto, initial-scale=1.0">
    <title>Receipt - {{ $transaction->transaction_number }}</title>
    <style>
        @page {
            margin: 0;
            size: auto;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #fff;
            color: #000;
            margin: 0;
            padding: 10px;
            width: 58mm;
            /* Standard thermal printer width */
            font-size: 12px;
            line-height: 1.2;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mb-4 {
            margin-bottom: 16px;
        }

        .mt-2 {
            margin-top: 8px;
        }

        .mt-4 {
            margin-top: 16px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 2px 0;
            vertical-align: top;
        }

        .w-full {
            width: 100%;
        }

        .flex {
            display: flex;
        }

        .justify-between {
            justify-content: space-between;
        }

        .items-baseline {
            align-items: baseline;
        }

        @media print {
            body {
                width: 58mm;
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>

<body onload="window.print(); setTimeout(() => window.close(), 1000);">

    <div class="text-center font-bold mb-2" style="font-size: 16px;">
        Cash Sales
    </div>
    <div class="text-center mb-2">-- Reprint Receipt --</div>
    <div class="text-center font-bold mb-2">MAXUMAX STORE (LLP0027516-LGN)</div>

    <div class="text-center mb-4">
        Wholly owned by MAXUMAX PLT<br>
        Lot 1-35, 1st Floor,<br>
        Suria Sabah, Kota Kinabalu,<br>
        Sabah, Malaysia
    </div>

    <div class="divider"></div>

    <table class="mb-2">
        <tr>
            <td style="width: 40%;">Document No. :</td>
            <td class="text-left">{{ $transaction->transaction_number }}</td>
        </tr>
        <tr>
            <td>Date :</td>
            <td class="text-left">
                {{ $transaction->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y') }}<br>
                {{ $transaction->created_at->setTimezone('Asia/Jakarta')->format('h:i:s A') }}
            </td>
        </tr>
        <tr>
            <td>Member :</td>
            <td></td>
        </tr>
        <tr>
            <td>Terminal :</td>
            <td>T01</td>
        </tr>
        <tr>
            <td>Cashier :</td>
            <td>CASHIER</td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Header columns -->
    <table style="font-weight:bold; margin-bottom: 4px;">
        <tr>
            <td style="width: 40%">DESC</td>
            <td class="text-right" style="width: 20%">U. PRICE</td>
            <td class="text-right" style="width: 15%">QTY</td>
            <td class="text-right" style="width: 10%">Disc</td>
            <td class="text-right" style="width: 15%">AMOUNT</td>
        </tr>
    </table>

    <div class="divider mt-0 mb-2"></div>

    <!-- Items -->
    @foreach($transaction->items as $item)
        <div class="mb-2">
            <div>{{ $item->product_name }} {{ $item->size ? '(' . $item->size . ')' : '' }}</div>
            <table style="width: 100%;">
                <tr>
                    <td style="width:40%;">{{ $item->item_code ?? 'PACK' }}</td>
                    <td class="text-right" style="width:20%;">{{ number_format($item->unit_price, 2) }}*</td>
                    <td class="text-right" style="width:15%;">{{ $item->quantity }}</td>
                    <td class="text-right" style="width:10%;">0.00</td>
                    <td class="text-right" style="width:15%;">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            </table>
        </div>
    @endforeach

    <div class="divider"></div>

    <div class="flex justify-between items-baseline mb-2">
        <div style="width: 50%;">
            <div>Item : {{ $transaction->items->count() }}</div>
            <div>Qty : {{ $transaction->items->sum('quantity') }}</div>
            <div>Total Saving : 0.00</div>
        </div>
        <div style="width: 50%;">
            <table class="text-right">
                <tr>
                    <td class="text-left pl-2">Sub Total :</td>
                    <td>{{ number_format($transaction->subtotal, 2) }}</td>
                </tr>
                <!-- Custom global discount row, specific to requirements -->
                @if($transaction->discount > 0)
                    <tr>
                        <td class="text-left pl-2">Discount :</td>
                        <td>-{{ number_format($transaction->discount, 2) }}</td>
                    </tr>
                @endif
                <tr>
                    <td class="text-left pl-2">Rounding :</td>
                    <td>0.00</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="flex justify-between mb-4 font-bold" style="font-size: 14px;">
        <div>Rounded Total (BND):</div>
        <div>{{ number_format($transaction->total_amount, 2) }}</div>
    </div>

    <div class="divider"></div>

    <div class="flex justify-between mb-2 mt-4 font-bold">
        <div>{{ $transaction->payment_method ?? 'Cash' }} (Paid):</div>
        <div class="text-right">{{ number_format($transaction->paid_amount, 2) }}</div>
    </div>

    <div class="flex justify-between mb-4 mt-2">
        <div>Change:</div>
        <div class="text-right">{{ number_format($transaction->change_amount, 2) }}</div>
    </div>

    <div class="text-center mb-4">
        Thank You For Shopping With Us!
    </div>

    <div class="text-center mb-4 text-xs" style="font-size: 10px;">
        Unused items with unremoved tags are eligible for<br>
        design and/or size exchanges within the first<br>
        3 days of purchase. Terms and conditions apply.<br>
        Thank you.
    </div>

    <div class="text-center text-xs" style="font-size: 10px;">
        Tel: 088-276 096 / 014-343 6496<br>
        Custom Order: 014-654 6496<br>
        www.maxumax.my
    </div>

</body>

</html>