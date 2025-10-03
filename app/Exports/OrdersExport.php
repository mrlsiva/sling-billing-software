<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderPaymentDetail;
use App\Models\Payment;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class OrdersExport implements FromView
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function view(): View
    {
        return view('branches.exports.order', [
            'orders' => $this->orders
        ]);
    }
}
