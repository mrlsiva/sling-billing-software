<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }} | Invoice</title>
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/png">
    <style>
        @@page {
            size: A4 portrait;
            margin: 10mm 8mm 18mm 8mm;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* ── Computer generated notice row ── */
        .cg-row td {
            border-top: 1px solid #000;
            text-align: center;
            font-size: 10px;
            padding: 13px 8px;
            color: #333;
        }

        /* ── Main invoice table ── */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #000; /* outer table border — covers filler row edges */
        }
        .main-table td, .main-table th {
            border: 1px solid #000;
            padding: 8px 4px;
            vertical-align: top;
        }
        .main-table th {
            background: #e8e8e8;
            text-align: center;
            font-size: 10px;
            font-weight: bold;
        }

        /* thead repeats on every page in both browser print and DomPDF */
        thead { display: table-header-group; }

        /* ── Column widths (total = 100%) ── */
        .c1  { width:  1%; }   /* S.No        — narrow  */
        .c2  { width: 40%; }   /* Description — widest  */
        .c3  { width:  1%; }   /* Qty         — narrow  */
        .c4  { width:  8%; }   /* Rate        — fit     */
        .c5  { width:  9%; }   /* Taxable     — fit     */
        .c6  { width:  7%; }   /* CGST %      — fit     */
        .c7  { width:  9%; }   /* CGST Amt    — fit     */
        .c8  { width:  7%; }   /* SGST %      — fit     */
        .c9  { width:  9%; }   /* SGST Amt    — fit     */
        .c10 { width:  9%; }   /* Amount      — fit     */

        /* ── Header inner tables ── */
        .hdr-wrap {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        .hdr-wrap td {  padding: 4px 7px; vertical-align: middle; }
        .buyer-wrap {
            width: 100%;
            border-collapse: collapse;
            /* border: 1px solid #000; */
            border-top: none;
        }
        .buyer-wrap td { border: 0; border-top: none; padding: 3px 7px; vertical-align: top; font-size: 10px; }

        /* ── Inner label tables ── */
        .inner { width: 100%; border-collapse: collapse; }
        .inner td { border: none; padding: 1px 3px; vertical-align: top; }

        /* ── Body rows ── */
        .main-table tbody tr { page-break-inside: avoid; }
        .total-row td { background: #f0f0f0; font-weight: bold; border-top: 1.5px solid #000 !important; }

        /* ── Summary section (last page, normal flow) ── */
        .summ-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summ-table td {
            border: 1px solid #000;
            border-top: none;
            padding: 6px 8px;
            vertical-align: top;
        }
        .tax-inner { width: 55%; border-collapse: collapse; }
        .tax-inner td { border: 1px solid #888; padding: 3px 6px; font-size: 10px; }

        /* ── Utilities ── */
        .center { text-align: center; }
        .right  { text-align: right;  }
        .bold   { font-weight: bold;  }
        .no-border { border: none !important; }
        .logo-img  { width: 240px; height: auto; }
    </style>
</head>
<body>

@php
    /* ── Number to Words (Indian system) ── */
    function numToWords(float $number): string {
        $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
                 'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
                 'Seventeen','Eighteen','Nineteen'];
        $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];
        $n = (int) floor($number);
        if ($n === 0) return 'Zero';
        $words = '';
        if ($n >= 10000000) { $words .= numToWords(floor($n / 10000000)) . ' Crore ';    $n %= 10000000; }
        if ($n >= 100000)   { $words .= numToWords(floor($n / 100000))   . ' Lakh ';     $n %= 100000;   }
        if ($n >= 1000)     { $words .= numToWords(floor($n / 1000))     . ' Thousand '; $n %= 1000;     }
        if ($n >= 100)      { $words .= $ones[floor($n / 100)] . ' Hundred '; $n %= 100; }
        if ($n >= 20)       { $words .= $tens[floor($n / 10)] . ' '; $n %= 10; }
        if ($n > 0)         { $words .= $ones[$n] . ' '; }
        return trim($words);
    }

    $netAmount     = ($order->order_discount && $order->order_discount > 0)
                     ? $order->bill_amount - $order->order_discount
                     : $order->bill_amount;

    $amountInWords = 'Rupees ' . numToWords($netAmount) . ' Only';

    $totalQty      = $order_details->sum(fn($d) => (int)$d->quantity);
    $totalTaxable  = $order_details->sum(fn($d) => ((float)$d->price - (float)$d->tax_amount) * (int)$d->quantity);
    $totalCgst     = $order_details->sum(fn($d) => ((float)$d->tax_amount * (int)$d->quantity) / 2);
    $totalSgst     = $totalCgst;
    $totalAmount   = $order_details->sum(fn($d) => (float)$d->price * (int)$d->quantity);

    $taxGroups     = App\Models\OrderDetail::select(
                         'tax_percent',
                         \DB::raw('SUM(CAST(tax_amount AS DECIMAL(10,4)) * quantity) as total_tax')
                     )->where('order_id', $order->id)->groupBy('tax_percent')->get();

    $grandTotalTax = $order_details->sum(fn($d) => (float)$d->tax_amount * (int)$d->quantity);

    $user_detail   = App\Models\UserDetail::where('user_id',
                         $order->branch_id ?? $order->shop_id
                     )->first();

    /* ── Filler rows to push summary to page bottom ──
       A4 content area ≈ 265mm. Header ≈ 54mm, summary ≈ 90mm.
       Each row ≈ 8mm → fill remaining ~121mm → ~15 rows minimum.
       When products overflow the page, emptyRowCount = 0 (no filler). */
    $minRows       = 15;
    $emptyRowCount = max(0, $minRows - $order_details->count());
@endphp

<table class="main-table">
<colgroup>
    <col style="width:4%">   {{-- S.No --}}
    <col style="width:35%">  {{-- Description --}}
    <col style="width:3%">   {{-- Qty --}}
    <col style="width:8%">   {{-- Rate --}}
    <col style="width:9%">   {{-- Taxable --}}
    <col style="width:7%">   {{-- CGST % --}}
    <col style="width:9%">   {{-- CGST Amt --}}
    <col style="width:7%">   {{-- SGST % --}}
    <col style="width:9%">   {{-- SGST Amt --}}
    <col style="width:9%">   {{-- Amount --}}
</colgroup>

    {{-- ════ THEAD: repeats on every page (browser print + DomPDF) ════ --}}
    <thead>
        {{-- Row 1: header block (company + invoice meta + buyer) --}}
        <tr>
            <td colspan="10" class="no-border" style="padding:0;">

                {{-- Company + Title + Invoice meta --}}
                <table class="hdr-wrap">
                    <tr style="border-bottom: 1px solid #000;">
                        <td style="width:30%; text-align:center;">
                            @if($user->logo)
                                <img src="{{ asset('storage/' . $user->logo) }}" class="logo-img" alt="Logo">
                            @endif
                        </td>
                        <td style="width:40%; text-align:center; border-left:1px solid #000; border-right:1px solid #000;">
                            <div style="font-size:17px; font-weight:bold; letter-spacing:1px;">TAX INVOICE</div>
                            <div style="font-size:12px; font-weight:bold; margin:2px 0;">{{ strtoupper($user->name) }}</div>
                            <div style="font-size:9px; line-height:1.5;">
                                {{ $user->user_detail->address }}<br>
                                CELL: {{ $user->phone }}@if($user->alt_phone != null) / {{ $user->alt_phone }}@endif
                                @if($user->email != null) &nbsp;|&nbsp; {{ $user->email }}@endif
                            </div>
                            @if($user->user_detail->gst != null)
                                <div style="font-size:9px; margin-top:2px;"><strong>GSTIN: {{ $user->user_detail->gst }}</strong></div>
                            @endif
                        </td>
                        <td style="width:30%; vertical-align:middle;">
                            <table class="inner" style="font-size:10px;">
                                <tr>
                                    <td style="width:42%;"><strong>Invoice No</strong></td>
                                    <td>: <strong>{{ $order->bill_id }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Date</strong></td>
                                    <td>: <strong>{{ \Carbon\Carbon::parse($order->billed_on)->format('d-m-Y') }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Time</strong></td>
                                    <td>: {{ \Carbon\Carbon::parse($order->billed_on)->format('h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Payment</strong></td>
                                    <td>: {{ $order_payment_details->map(fn($o) => $o->payment->name)->join(', ') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                {{-- Buyer + Sales info --}}
                <table class="buyer-wrap">
                    <tr>
                        <td style="width:55%; border-right:1px solid #000;">
                            <table class="inner">
                                <tr>
                                    <td style="width:30%;"><strong>Buyer</strong></td>
                                    <td>: <strong>{{ $order->customer->name }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Address</strong></td>
                                    <td>: {{ $order->customer->address }}@if($order->customer->pincode != null), {{ $order->customer->pincode }}@endif</td>
                                </tr>
                                <tr>
                                    <td><strong>Mobile</strong></td>
                                    <td>: {{ $order->customer->phone }}</td>
                                </tr>
                                @if($order->customer->gst != null)
                                <tr>
                                    <td><strong>GSTIN</strong></td>
                                    <td>: {{ $order->customer->gst }}</td>
                                </tr>
                                @endif
                            </table>
                        </td>
                        <td style="width:45%;">
                            <table class="inner">
                                <tr>
                                    <td style="width:42%;"><strong>Sales Person</strong></td>
                                    <td>: {{ $order->billedBy->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Terms of Pay</strong></td>
                                    <td>: &#x2014;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>

        {{-- Row 2: column headers --}}
        <tr>
            <th class="c1">S.No</th>
            <th class="c2">Description of Goods</th>
            <th class="c3">Qty</th>
            <th class="c4">Rate (&#x20B9;)</th>
            <th class="c5">Taxable (&#x20B9;)</th>
            <th class="c6">CGST %</th>
            <th class="c7">CGST Amt</th>
            <th class="c8">SGST %</th>
            <th class="c9">SGST Amt</th>
            <th class="c10">Amount (&#x20B9;)</th>
        </tr>
    </thead>

    {{-- ════ TBODY: product rows ════ --}}
    <tbody>
        @foreach($order_details as $order_detail)
        @php
            $basePrice     = (float)$order_detail->price - (float)$order_detail->tax_amount;
            $qty           = (int)$order_detail->quantity;
            $taxableAmt    = $basePrice * $qty;
            $cgstVal       = ((float)$order_detail->tax_amount * $qty) / 2;
            $sgstVal       = $cgstVal;
            $totalAmt      = (float)$order_detail->price * $qty;
            $sizeName      = $order_detail->size_id   ? $order_detail->size->name   : null;
            $colorName     = $order_detail->colour_id ? $order_detail->colour->name : null;
            $variationText = ($sizeName || $colorName)
                ? '(' . trim(($sizeName ?? '') . ' - ' . ($colorName ?? ''), ' - ') . ')'
                : '';
        @endphp
        <tr>
            <td class="c1 center">{{ $loop->iteration }}</td>
            <td class="c2">
                {{ $order_detail->name }}{{ $variationText ? ' ' . $variationText : '' }}
                @if(!empty($order_detail->imei))
                    <br><small style="font-size:9px;">IMEI: {{ $order_detail->imei }}</small>
                @endif
            </td>
            <td class="c3 center">{{ $qty }}</td>
            <td class="c4 right">{{ number_format($basePrice, 2) }}</td>
            <td class="c5 right">{{ number_format($taxableAmt, 2) }}</td>
            <td class="c6 center">{{ number_format((float)$order_detail->tax_percent / 2, 2) }}</td>
            <td class="c7 right">{{ number_format($cgstVal, 2) }}</td>
            <td class="c8 center">{{ number_format((float)$order_detail->tax_percent / 2, 2) }}</td>
            <td class="c9 right">{{ number_format($sgstVal, 2) }}</td>
            <td class="c10 right">{{ number_format($totalAmt, 2) }}</td>
        </tr>
        @endforeach

        {{-- Filler rows — keep vertical column lines, remove horizontal row lines --}}
        @for($i = 0; $i < $emptyRowCount; $i++)
        <tr style="height:8mm;">
            <td class="c1"  style="border-top:none; border-bottom:none;"></td>
            <td class="c2"  style="border-top:none; border-bottom:none;"></td>
            <td class="c3"  style="border-top:none; border-bottom:none;"></td>
            <td class="c4"  style="border-top:none; border-bottom:none;"></td>
            <td class="c5"  style="border-top:none; border-bottom:none;"></td>
            <td class="c6"  style="border-top:none; border-bottom:none;"></td>
            <td class="c7"  style="border-top:none; border-bottom:none;"></td>
            <td class="c8"  style="border-top:none; border-bottom:none;"></td>
            <td class="c9"  style="border-top:none; border-bottom:none;"></td>
            <td class="c10" style="border-top:none; border-bottom:none;"></td>
        </tr>
        @endfor

        {{-- Totals row --}}
        <tr class="total-row">
            <td class="c1 center">&#x2014;</td>
            <td class="c2 center bold">TOTAL</td>
            <td class="c3 center bold">{{ $totalQty }}</td>
            <td class="c4"></td>
            <td class="c5 right bold">{{ number_format($totalTaxable, 2) }}</td>
            <td class="c6"></td>
            <td class="c7 right bold">{{ number_format($totalCgst, 2) }}</td>
            <td class="c8"></td>
            <td class="c9 right bold">{{ number_format($totalSgst, 2) }}</td>
            <td class="c10 right bold">{{ number_format($totalAmount, 2) }}</td>
        </tr>
    </tbody>

</table>

{{-- ════ SUMMARY — appears after all product rows, last page only ════ --}}
<table class="summ-table" style="page-break-inside:avoid;">

    {{-- Tax Summary | Net Total --}}
    <tr>
        <td style="width:55%; vertical-align:top;">
            <div class="bold" style="font-size:11px; margin-bottom:5px;">TAX SUMMARY</div>
            <table class="tax-inner">
                <tr>
                    <td class="bold" style="width:35%;">Tax Type</td>
                    <td class="bold center" style="width:30%;">Rate %</td>
                    <td class="bold right" style="width:35%;">Amount (&#x20B9;)</td>
                </tr>
                @foreach($taxGroups as $tax)
                <tr>
                    <td>CGST</td>
                    <td class="center">{{ number_format($tax->tax_percent / 2, 2) }}%</td>
                    <td class="right">{{ number_format($tax->total_tax / 2, 2) }}</td>
                </tr>
                <tr>
                    <td>SGST</td>
                    <td class="center">{{ number_format($tax->tax_percent / 2, 2) }}%</td>
                    <td class="right">{{ number_format($tax->total_tax / 2, 2) }}</td>
                </tr>
                @endforeach
                <tr style="background:#f0f0f0;">
                    <td colspan="2" class="bold">Total Tax</td>
                    <td class="right bold">{{ number_format($grandTotalTax, 2) }}</td>
                </tr>
            </table>
        </td>
        <td style="width:45%; vertical-align:middle; text-align:right; padding:10px 12px;">
            @if($order->order_discount != null && $order->order_discount > 0)
            <div style="font-size:11px; margin-bottom:6px;">
                Discount : <strong>&#x20B9; {{ number_format($order->order_discount, 2) }}</strong>
            </div>
            @endif
            <div style="font-size:11px; color:#555; margin-bottom:4px;">Net Total (Incl. Tax)</div>
            <div style="font-size:20px; font-weight:bold;">&#x20B9; {{ number_format($netAmount, 2) }}</div>
        </td>
    </tr>

    {{-- Amount in words --}}
    <tr>
        <td colspan="2" style="font-size:10px; padding:5px 8px;">
            Amount Chargeable (in words):
            <strong>{{ $amountInWords }}</strong>
            <span style="float:right; font-style:italic; color:#555;">E. &amp; O.E.</span>
        </td>
    </tr>

    {{-- Bank details + Declaration | Authorised Signatory --}}
    <tr style="page-break-inside:avoid;">
        <td style="vertical-align:top; padding:8px;">
            @if($user_detail && $user_detail->show_bank_detail == 1)
                <strong>Bank Details</strong><br>
                <span style="font-size:10px; line-height:1.8;">
                    Name &nbsp;&nbsp;: {{ $user->bank_detail->name ?? '-' }}<br>
                    Branch : {{ $user->bank_detail->branch ?? '-' }}<br>
                    A/C No : {{ $user->bank_detail->account_no ?? '-' }}<br>
                    IFSC &nbsp;&nbsp;: {{ $user->bank_detail->ifsc_code ?? '-' }}
                </span><br><br>
            @endif
            <u><strong>Declaration</strong></u><br>
            <span style="font-size:10px; line-height:1.7;">
                We declare that this invoice shows the actual price of the goods
                described and that all particulars are true and correct.
            </span>
        </td>
        <td style="text-align:right; vertical-align:top; padding:8px;">
            <strong>for {{ strtoupper($user->name) }}</strong>
            <br><br><br><br>
            <strong>Authorised Signatory</strong>
        </td>
    </tr>

    {{-- Computer generated notice — last row, stays with declaration --}}
    <tr class="cg-row">
        <td colspan="2">This is a Computer Generated Invoice</td>
    </tr>

</table>

<script>
    window.onload = function () {
        setTimeout(function () { window.print(); }, 500);
    };
</script>

</body>
</html>
