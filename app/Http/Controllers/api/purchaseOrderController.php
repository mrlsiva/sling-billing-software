<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\ResponseHelper;
use App\Traits\Notifications;
use Illuminate\Http\Request;
use App\Imports\PurchaseBulkImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\VendorPaymentDetail;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderRefund;
use App\Models\ProductImeiNumber;
use App\Models\PurchaseOrder;
use App\Models\ShopPayment;
use App\Models\StockVariation;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Vendor;
use App\Models\Tax;
use App\Traits\Log;
use Carbon\Carbon;
use DB;

class purchaseOrderController extends Controller
{
    use Log, Notifications, ResponseHelper;

    public function list(Request $request)
    {
        $purchase_orders = PurchaseOrder::with('vendor')
            ->where('shop_id', Auth::user()->owner_id)
            ->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')
                    ->from('purchase_orders')
                    ->where('shop_id', Auth::user()->owner_id)
                    ->groupBy('invoice_no');
            })
            ->when($request->vendor, function ($query, $vendor) {
                $query->whereHas('vendor', function ($q) use ($vendor) {
                    $q->where('name', 'like', "%{$vendor}%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        foreach ($purchase_orders as $po) {
            $allItems = PurchaseOrder::where('invoice_no', $po->invoice_no)
                ->where('shop_id', Auth::user()->owner_id)
                ->get();

            $total  = $allItems->count();
            $paid   = $allItems->where('status', 1)->count();
            $unpaid = $allItems->where('status', 0)->count();

            if ($po->due_date && Carbon::parse($po->due_date)->lt(Carbon::today()) && $paid != $total) {
                $po->computed_status = 'overdue';
            } elseif ($paid == $total) {
                $po->computed_status = 'paid';
            } elseif ($unpaid == $total) {
                $po->computed_status = 'unpaid';
            } else {
                $po->computed_status = 'partial';
            }
        }

        return $this->successResponse($purchase_orders, 200, 'Purchase orders retrieved successfully.');
    }

    public function create_data(Request $request)
    {
        $vendors    = Vendor::where('shop_id', Auth::user()->owner_id)->get();
        $shop_payment_ids = ShopPayment::where([['shop_id', Auth::user()->owner_id], ['is_active', 1]])->pluck('payment_id')->toArray();
        $payments   = Payment::whereIn('id', $shop_payment_ids)->get();
        $categories = Category::where([['user_id', Auth::user()->owner_id], ['is_active', 1]])->get();
        $taxes      = Tax::where([['shop_id', Auth::user()->owner_id], ['is_active', 1]])->get();

        return $this->successResponse(
            compact('vendors', 'payments', 'categories', 'taxes'),
            200,
            'Create form data retrieved successfully.'
        );
    }

    public function get_categories(Request $request)
    {
        $categories = Category::where([['user_id', Auth::user()->owner_id], ['is_active', 1]])->get();
        return $this->successResponse($categories, 200, 'Categories retrieved successfully.');
    }

    public function get_product(Request $request)
    {
        $products = Product::where([
            ['user_id', Auth::user()->owner_id],
            ['category_id', $request->category],
            ['sub_category_id', $request->sub_category],
            ['is_active', 1],
        ])->get();

        return $this->successResponse($products, 200, 'Products retrieved successfully.');
    }

    public function get_product_detail(Request $request)
    {
        $product = Product::with('metric', 'tax')->where('id', $request->product)->first();

        if (!$product) {
            return $this->errorResponse([], 404, 'Product not found.');
        }

        return $this->successResponse($product, 200, 'Product detail retrieved successfully.');
    }

    public function get_stock_variations(Request $request)
    {
        $stock = Stock::where('product_id', $request->product_id)
            ->where('shop_id', Auth::user()->owner_id)
            ->whereNull('branch_id')
            ->first();

        if (!$stock) {
            return $this->successResponse(['stock_id' => null, 'variations' => []], 200, 'No stock found.');
        }

        $variations = StockVariation::with(['size', 'colour'])
            ->where('stock_id', $stock->id)
            ->get();

        return $this->successResponse(
            ['stock_id' => $stock->id, 'variations' => $variations],
            200,
            'Stock variations retrieved successfully.'
        );
    }

    public function get_product_stock(Request $request)
    {
        $stockRow = Stock::where('product_id', $request->product)
            ->where('shop_id', Auth::user()->owner_id)
            ->whereNull('branch_id')
            ->select('quantity')
            ->first();

        $stock = $stockRow ? $stockRow->quantity : 0;

        return $this->successResponse(['stock' => $stock], 200, 'Product stock retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'vendor'                          => 'required',
            'invoice'                         => [
                'required',
                Rule::unique('purchase_orders', 'invoice_no')->where(function ($query) use ($request) {
                    return $query->where('vendor_id', $request->vendor)
                                 ->where('shop_id', Auth::user()->owner_id);
                }),
            ],
            'invoice_date'                    => 'required|date',
            'due_date'                        => 'nullable|date|after_or_equal:invoice_date',
            'products'                        => 'required|array|min:1',
            'products.*.category'             => 'required',
            'products.*.sub_category'         => 'required',
            'products.*.product'              => 'required',
            'products.*.unit'                 => 'required',
            'products.*.quantity'             => 'required|numeric|min:1',
            'products.*.price_per_unit'       => 'required|numeric|min:0.01',
            'products.*.net_cost'             => 'required|numeric',
            'products.*.gross_cost'           => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        // Validate IMEI numbers across all products
        $allImeis = [];
        foreach ($request->products as $item) {
            if (!empty($item['imei'])) {
                $imeiList = is_array($item['imei']) ? $item['imei'] : explode(',', $item['imei']);

                foreach ($imeiList as $imei) {
                    $imei = trim($imei);
                    if ($imei === '') continue;

                    if (!preg_match('/^[0-9]{1,15}$/', $imei)) {
                        return $this->errorResponse([], 422, "IMEI number $imei must not exceed 15 digits.");
                    }

                    if (in_array($imei, $allImeis)) {
                        return $this->errorResponse([], 422, "IMEI number $imei is duplicated across products.");
                    }

                    $allImeis[] = $imei;
                }
            }
        }

        DB::beginTransaction();

        try {
            [$purchaseOrder, $totalGross, $vendor] = $this->processOrderStorage(
                $request->vendor,
                $request->payment,
                $request->invoice,
                $request->invoice_date,
                $request->due_date,
                $request->products
            );

            $prepaid = $vendor->prepaid_amount ?? 0;

            if ($prepaid > 0) {
                $allocatable = 0;
                $comment     = '';
                $status      = 0;

                if ($prepaid >= $totalGross) {
                    $allocatable = $totalGross;
                    $vendor->update(['prepaid_amount' => $prepaid - $totalGross]);
                    $comment = 'Fully paid using prepaid balance';
                    $status  = 1;
                } else {
                    $allocatable = $prepaid;
                    $vendor->update(['prepaid_amount' => 0]);
                    $comment = 'Partially paid using prepaid balance';
                    $status  = 2;
                }

                VendorPaymentDetail::create([
                    'vendor_payment_id' => null,
                    'purchase_order_id' => $purchaseOrder->id,
                    'payment_id'        => 1,
                    'amount'            => $allocatable,
                    'paid_on'           => now(),
                    'comment'           => $comment,
                ]);

                PurchaseOrder::where('invoice_no', $request->invoice)->update(['status' => $status]);
            }

            DB::commit();

            return $this->successResponse(
                ['purchase_order_id' => $purchaseOrder->id],
                200,
                'Purchase order created successfully.'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse([], 500, 'Error creating purchase order: ' . $e->getMessage());
        }
    }

    public function processOrderStorage($vendorId, $paymentId, $invoiceNo, $invoiceDate, $dueDate, array $products)
    {
        $vendor        = Vendor::findOrFail($vendorId);
        $totalGross    = 0;
        $purchaseOrder = null;

        foreach ($products as $item) {
            $imeiList   = !empty($item['imei']) ? (is_array($item['imei']) ? $item['imei'] : explode(',', $item['imei'])) : [];
            $sizeList   = [];
            $colourList = [];

            if (!empty($item['variation']) && is_array($item['variation'])) {
                foreach ($item['variation'] as $var) {
                    if (!empty($var['size_id']))   $sizeList[]   = $var['size_id'];
                    if (!empty($var['colour_id'])) $colourList[] = $var['colour_id'];
                }
            }

            $sizeList   = array_unique($sizeList);
            $colourList = array_unique($colourList);
            $imeiList   = array_filter(array_map('trim', $imeiList));

            $purchaseOrder = PurchaseOrder::create([
                'shop_id'         => Auth::user()->owner_id,
                'vendor_id'       => $vendorId,
                'payment_id'      => $paymentId,
                'invoice_no'      => $invoiceNo,
                'invoice_date'    => $invoiceDate,
                'due_date'        => $dueDate,
                'category_id'     => $item['category'],
                'sub_category_id' => $item['sub_category'],
                'product_id'      => $item['product'],
                'metric_id'       => $item['unit'],
                'quantity'        => $item['quantity'],
                'price_per_unit'  => $item['price_per_unit'],
                'tax'             => $item['tax'] ?? 0,
                'discount'        => $item['discount'] ?? 0,
                'net_cost'        => $item['net_cost'],
                'gross_cost'      => $item['gross_cost'],
                'imei'            => !empty($imeiList) ? implode(',', $imeiList) : null,
                'size'            => !empty($sizeList) ? implode(',', $sizeList) : null,
                'colour'          => !empty($colourList) ? implode(',', $colourList) : null,
                'status'          => 0,
            ]);

            $product = Product::find($item['product']);
            $product->increment('quantity', $item['quantity']);

            $stock = Stock::where([
                'shop_id'         => Auth::user()->owner_id,
                'branch_id'       => null,
                'category_id'     => $item['category'],
                'sub_category_id' => $item['sub_category'],
                'product_id'      => $item['product'],
            ])->first();

            if ($stock) {
                $existingImeis = !empty($stock->imei) ? explode(',', $stock->imei) : [];
                $mergedImeis   = array_filter(array_map('trim', array_unique(array_merge($existingImeis, $imeiList))));
                $stock->update([
                    'quantity' => $stock->quantity + $item['quantity'],
                    'imei'     => !empty($mergedImeis) ? implode(',', $mergedImeis) : null,
                ]);
            } else {
                $stock = Stock::create([
                    'shop_id'         => Auth::user()->owner_id,
                    'branch_id'       => null,
                    'category_id'     => $item['category'],
                    'sub_category_id' => $item['sub_category'],
                    'product_id'      => $item['product'],
                    'quantity'        => $item['quantity'],
                    'imei'            => !empty($imeiList) ? implode(',', $imeiList) : null,
                ]);
            }

            if (!empty($item['imei'])) {
                $imeiNumbers = array_filter(array_map('trim', is_array($item['imei']) ? $item['imei'] : explode(',', $item['imei'])));
                foreach ($imeiNumbers as $imei) {
                    ProductImeiNumber::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id'        => $item['product'],
                        'name'              => $imei,
                        'is_sold'           => 0,
                    ]);
                }
            }

            $quantity = (float) $item['quantity'];

            if (!empty($item['variation']) && is_array($item['variation'])) {
                foreach ($item['variation'] as $var) {
                    $stockId  = $var['stock_id'];
                    $sizeId   = $var['size_id'] ?? null;
                    $colourId = $var['colour_id'] ?? null;
                    $qty      = (int) ($var['qty'] ?? 0);

                    if ($qty <= 0) continue;

                    $variation = StockVariation::where('stock_id', $stockId)
                        ->where('product_id', $item['product'])
                        ->where(function ($q) use ($sizeId) {
                            $sizeId === null ? $q->whereNull('size_id') : $q->where('size_id', $sizeId);
                        })
                        ->where(function ($q) use ($colourId) {
                            $colourId === null ? $q->whereNull('colour_id') : $q->where('colour_id', $colourId);
                        })
                        ->lockForUpdate()
                        ->first();

                    if (!$variation) {
                        $variation = StockVariation::create([
                            'stock_id'   => $stockId,
                            'product_id' => $item['product'],
                            'size_id'    => $sizeId,
                            'colour_id'  => $colourId,
                            'quantity'   => 0,
                            'price'      => $product->price,
                        ]);
                    }

                    $variation->update([
                        'quantity' => $variation->quantity + $qty,
                        'price'    => $product->price,
                    ]);
                }
            } else {
                $defaultVariation = StockVariation::where('stock_id', $stock->id)
                    ->where('product_id', $item['product'])
                    ->whereNull('size_id')
                    ->whereNull('colour_id')
                    ->lockForUpdate()
                    ->first();

                if ($defaultVariation) {
                    $defaultVariation->update([
                        'quantity' => $defaultVariation->quantity + $quantity,
                        'price'    => $product->price,
                    ]);
                }
            }

            $totalGross += $item['gross_cost'];

            $this->addToLog($this->unique(), Auth::user()->id, 'Purchase Order Created', 'App/Models/PurchaseOrder', 'purchase_orders', $purchaseOrder->id, 'Insert', null, json_encode($item), 'Success', 'Purchase Order Created');
            $this->notification(Auth::user()->owner_id, null, 'App/Models/PurchaseOrder', $purchaseOrder->id, null, json_encode($item), now(), Auth::user()->id, 'Purchase order created for product ' . $product->name . ' done successfully with quantity ' . $item['quantity'], null, null, 7);
        }

        return [$purchaseOrder, $totalGross, $vendor];
    }

    public function detail(Request $request, $id)
    {
        $purchase_order = PurchaseOrder::find($id);

        if (!$purchase_order) {
            return $this->errorResponse([], 404, 'Purchase order not found.');
        }

        $purchase_orders = PurchaseOrder::with(['vendor', 'category', 'sub_category', 'product', 'metric'])
            ->where('shop_id', Auth::user()->owner_id)
            ->where('invoice_no', $purchase_order->invoice_no)
            ->paginate(10);

        return $this->successResponse($purchase_orders, 200, 'Purchase order detail retrieved successfully.');
    }

    public function update(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'old_amount'        => 'required|numeric',
            'new_amount'        => 'required|numeric|min:0',
            'reason'            => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        $purchase = PurchaseOrder::with('vendor')->findOrFail($request->purchase_order_id);

        DB::beginTransaction();

        try {
            $oldAmount = (float) $request->old_amount;
            $newAmount = (float) $request->new_amount;

            $purchase->update(['gross_cost' => $newAmount]);

            $purchase_order_detail = PurchaseOrderDetail::create([
                'purchase_order_id' => $purchase->id,
                'old_amount'        => $oldAmount,
                'new_amount'        => $newAmount,
                'updated_on'        => Carbon::now(),
                'comment'           => $request->reason,
            ]);

            $this->addToLog($this->unique(), Auth::user()->id, 'Purchase Order Updated', 'App/Models/PurchaseOrderDetail', 'purchase_order_details', $purchase_order_detail->id, 'Insert', null, $request, 'Success', 'Purchase Order Updated');
            $this->notification(Auth::user()->owner_id, null, 'App/Models/PurchaseOrder', $purchase->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Purchase order updated for product ' . $purchase->product->name . ' done successfully', null, null, 7);

            $paymentsForThis = VendorPaymentDetail::where('purchase_order_id', $purchase->id)->orderBy('id', 'desc')->get();
            $alreadyPaid     = (float) $paymentsForThis->sum('amount');

            if ($alreadyPaid > $newAmount) {
                $toFree        = $alreadyPaid - $newAmount;
                $freedByPayment = [];

                foreach ($paymentsForThis as $payRow) {
                    if ($toFree <= 0) break;

                    $rowAmt    = (float) $payRow->amount;
                    $reduce    = min($rowAmt, $toFree);
                    $newRowAmt = $rowAmt - $reduce;

                    if ($newRowAmt <= 0) {
                        $payRow->delete();
                    } else {
                        $payRow->amount = $newRowAmt;
                        $payRow->save();
                    }

                    $pid = $payRow->payment_id;
                    $freedByPayment[$pid] = ($freedByPayment[$pid] ?? 0) + $reduce;
                    $toFree -= $reduce;
                }

                $nextOrders = PurchaseOrder::where('vendor_id', $purchase->vendor_id)->where('status', '!=', 1)->orderBy('id')->get();

                foreach ($nextOrders as $next) {
                    if (array_sum($freedByPayment) <= 0) break;

                    $paidNext      = (float) VendorPaymentDetail::where('purchase_order_id', $next->id)->sum('amount');
                    $remainingNext = (float) $next->gross_cost - $paidNext;

                    if ($remainingNext <= 0) continue;

                    foreach ($freedByPayment as $pid => &$available) {
                        if ($available <= 0) continue;
                        if ($remainingNext <= 0) break;

                        $alloc    = min($available, $remainingNext);
                        $existing = VendorPaymentDetail::where('purchase_order_id', $next->id)->where('payment_id', $pid)->orderBy('id', 'desc')->first();

                        if ($existing) {
                            $existing->amount = (float) $existing->amount + $alloc;
                            $existing->save();
                        } else {
                            VendorPaymentDetail::create([
                                'vendor_payment_id' => null,
                                'purchase_order_id' => $next->id,
                                'payment_id'        => $pid,
                                'amount'            => $alloc,
                                'paid_on'           => now(),
                                'comment'           => 'Auto-adjusted from PO #' . $purchase->id,
                            ]);
                        }

                        $available     -= $alloc;
                        $remainingNext -= $alloc;
                    }
                    unset($available);

                    $totalPaidNext = (float) VendorPaymentDetail::where('purchase_order_id', $next->id)->sum('amount');
                    if ($totalPaidNext >= (float) $next->gross_cost) {
                        $next->update(['status' => 1]);
                    } elseif ($totalPaidNext > 0) {
                        $next->update(['status' => 3]);
                    } else {
                        $next->update(['status' => 0]);
                    }
                }

                $remainingFreed = array_sum($freedByPayment);
                if ($remainingFreed > 0) {
                    $vendor = $purchase->vendor;
                    $vendor->prepaid_amount = (float) ($vendor->prepaid_amount ?? 0) + $remainingFreed;
                    $vendor->save();
                }

                $currentPaid = (float) VendorPaymentDetail::where('purchase_order_id', $purchase->id)->sum('amount');
                if ($currentPaid >= $newAmount) {
                    $purchase->update(['status' => 1]);
                } elseif ($currentPaid > 0) {
                    $purchase->update(['status' => 3]);
                } else {
                    $purchase->update(['status' => 0]);
                }
            } else {
                if ($alreadyPaid >= $newAmount) {
                    $purchase->update(['status' => 1]);
                } elseif ($alreadyPaid > 0) {
                    $purchase->update(['status' => 3]);
                } else {
                    $purchase->update(['status' => 0]);
                }
            }

            DB::commit();

            return $this->successResponse(
                ['purchase_order_id' => $purchase->id],
                200,
                'Purchase order updated successfully.'
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse([], 500, 'Something went wrong: ' . $e->getMessage());
        }
    }

    public function refund(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'purchase_id'      => 'required|exists:purchase_orders,id',
            'vendor'           => 'required',
            'purchase_amount'  => 'required|numeric',
            'refund_quantity'  => 'required|numeric|min:1',
            'refund_amount'    => 'required|numeric|min:0',
            'comment'          => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationFailed($validator->errors(), 'Validation failed.');
        }

        DB::beginTransaction();

        try {
            $purchase = PurchaseOrder::where('id', $request->purchase_id)->first();

            $refund = PurchaseOrderRefund::create([
                'purchase_order_id' => $purchase->id,
                'vendor_id'         => $request->vendor,
                'old_amount'        => $request->purchase_amount,
                'quantity'          => $request->refund_quantity,
                'refunded_by'       => Auth::user()->id,
                'refund_amount'     => $request->refund_amount,
                'refund_on'         => Carbon::now(),
                'reason'            => $request->comment,
            ]);

            $paid = VendorPaymentDetail::where('purchase_order_id', $purchase->id)->first();
            if ($paid) {
                $refund->update(['need_to_deduct' => 1]);
            }

            $newQuantity = max(0, $purchase->quantity - $request->refund_quantity);
            $newAmount   = max(0, $purchase->gross_cost - $request->refund_amount);

            $purchase->update([
                'quantity'    => $newQuantity,
                'gross_cost'  => $newAmount,
                'is_refunded' => 1,
            ]);

            $purchase    = PurchaseOrder::where('id', $request->purchase_id)->first();
            $totalPaid   = VendorPaymentDetail::where('purchase_order_id', $purchase->id)->sum('amount');

            if ($totalPaid == 0) {
                $purchase->update(['status' => 0]);
            } elseif ($totalPaid >= $purchase->gross_cost) {
                $purchase->update(['status' => 1]);
            } else {
                $purchase->update(['status' => 2]);
            }

            $this->addToLog($this->unique(), Auth::user()->id, 'Purchase Order Refund', 'App/Models/PurchaseOrderRefund', 'purchase_order_refunds', $refund->id, 'Insert', null, json_encode($request->all()), 'Success', 'Purchase Order Refund');
            $this->notification(Auth::user()->owner_id, null, 'App/Models/PurchaseOrderRefund', $refund->id, null, json_encode($request->all()), now(), Auth::user()->id, 'Purchase order return for product ' . $purchase->product->name . ' done successfully', null, null, 7);

            DB::commit();

            return $this->successResponse(['refund_id' => $refund->id], 200, 'Purchase order refunded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse([], 500, 'Error processing refund: ' . $e->getMessage());
        }
    }
}