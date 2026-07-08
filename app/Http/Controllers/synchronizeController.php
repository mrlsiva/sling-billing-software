<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\QueueStock;

class synchronizeController extends Controller
{
    public function synchronize_stock(Request $request)
    {
        $filter = request()->get('filter', 'received');

        $latestIds = QueueStock::query()
            ->when($filter === 'transfer', fn($q) => $q->where('from', auth()->id()))
            ->when($filter === 'received', fn($q) => $q->where('to', auth()->id()))
            ->selectRaw('MAX(id) as id')
            ->groupBy('unique_id');

        $stocks = QueueStock::with(['From', 'To', 'initiatedBy', 'updatedBy'])
            ->whereIn('id', $latestIds)
            ->latest()
            ->paginate(10);

        return view('synchronize', compact('stocks'));
    }

    public function synchronize_view_stock(Request $request,$company,$id)
    {

        $transfer_detail = QueueStock::where('id',$id)->first();
        $transfer_products = QueueStock::where('id',$id)->get();

         return view('synchronize_bill',compact('transfer_detail','transfer_products'));
    }

    public function approve(Request $request,$company,$id)
    {

        $transfer_detail = QueueStock::where('id',$id)->first();
        $transfer_details = QueueStock::where('id',$transfer_detail->unique_id)->get();

        if($transfer_detail->type == 1)
        {
            $lastInvoice = ProductHistory::where('shop_id',$transfer_detail->from)->lockForUpdate()->max('invoice');

            $next = $lastInvoice ? ((int) ltrim($lastInvoice, '0') + 1) : 1;

            $invoice = str_pad($next, 5, '0', STR_PAD_LEFT);

            foreach ($transfer_details as $transfer_detail) 
            {
                DB::beginTransaction();

                // Update or create branch stock
                $branchStock = Stock::where([['branch_id', $transfer_detail->to],['product_id', $transfer_detail->product_id]])->first();

                if ($branchStock) 
                {
                    $branchImeis = [];

                    // If branch already has IMEIs, append
                    if ($branchStock && $branchStock->imei) {
                        $branchImeis = explode(',', $branchStock->imei);
                    }

                    // Merge existing + new IMEIs
                    $updatedBranchImeis = array_merge($branchImeis, $selectedImeis);

                    $product = Product::where('id',$transfer_detail->product_id)->first();

                    $branchStock->update([
                        'quantity'       => $branchStock->quantity + $request->quantity,
                        'is_active'      => 1,
                        'imei' => implode(',', $updatedBranchImeis)
                    ]);

                    //Log
                    $this->addToLog($this->unique(),Auth::user()->id,'Stock Updated','App/Models/Stock','stocks',$branchStock->id,'Update',null,$request,'Success','Stock Updated for this product');
                } 
                else 
                {
                    $branchStock = Stock::create([
                        'shop_id'        => $transfer_detail->from,
                        'branch_id'      => $transfer_detail->to,
                        'category_id'    => $transfer_detail->product->category_id,
                        'sub_category_id'=> $transfer_detail->product->sub_category_id,
                        'product_id'     => $transfer_detail->product->id,
                        'quantity'       => $transfer_detail->quantity,
                        'is_active'      => 1,
                        'imei'           => implode(',', $selectedImeis)
                    ]);

                    //Log
                    $this->addToLog($this->unique(),Auth::user()->id,'Stock Added','App/Models/Stock','stocks',$branchStock->id,'Insert',null,$request,'Success','Stock Added for this product');

                }

                // Deduct from main shop stock
                $mainStock = Stock::where([['shop_id', $transfer_detail->from],['branch_id', null],['product_id', $transfer_detail->product_id]])->first();

                
                if ($mainStock) 
                {
                    $mainImeis = [];

                    if ($mainStock && $mainStock->imei) {
                        $mainImeis = explode(',', $mainStock->imei);
                    }

                    // Remove transferred IMEIs from main shop IMEI list
                    $remainingImeis = array_diff($mainImeis, $selectedImeis);

                    $mainStock->update([
                        'quantity' => $mainStock->quantity - $transfer_detail->quantity,
                        'imei' => implode(',', $remainingImeis)
                    ]);
                }

                $transfer = ProductHistory::create([
                    'shop_id'        => $transfer_detail->from,
                    'invoice'        => $invoice,
                    'from'           => $transfer_detail->from,
                    'to'             => $transfer_detail->to,
                    'category_id'    => $transfer_detail->product->category_id,
                    'sub_category_id'=> $transfer_detail->product->sub_category_id,
                    'product_id'     => $transfer_detail->product->id,
                    'quantity'       => $transfer_detail->quantity,
                    'price'          => $transfer_detail->price,
                    'transfer_on'    => $transfer_detail->initiated_on,
                    'transfer_by'    => $transfer_detail->initiated_by,
                ]);

                //Log
                $this->addToLog($this->unique(),Auth::user()->id,'Product Transfer','App/Models/ProductHistory','product_histories',$transfer->id,'Create',null,$request,'Success','Product Transfered Successfully');

                //Notification
                $this->notification($transfer_detail->from, null,'App/Models/ProductHistory', $transfer->id, null, json_encode($transfer_detail), now(), Auth::user()->id, $transfer->product->name.' has been successfully transfered to branch '.$transfer->transfer_to->name,null, null,8);

                //Notification
                $this->notification(null, $transfer_detail->to,'App/Models/ProductHistory', $transfer->id, null, json_encode($transfer_detail), now(), Auth::user()->id, $transfer->product->name.' has been successfully transfered to your branch '.$transfer->transfer_to->name,null, null,8);

                DB::commit();

                return redirect()->back()->with('toast_success', 'Product transferred approved successfully.');


            }

        }

    }

    public function reject(Request $request,$company,$id)
    {
        
    }
}
