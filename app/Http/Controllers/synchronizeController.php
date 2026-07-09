<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            ->orderBy('status', 'asc')
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
        $transfer_details = QueueStock::where('unique_id',$transfer_detail->unique_id)->get();

        if($transfer_detail->type == 1)
        {
            $lastInvoice = ProductHistory::where('shop_id',$transfer_detail->from)->lockForUpdate()->max('invoice');

            $next = $lastInvoice ? ((int) ltrim($lastInvoice, '0') + 1) : 1;

            $invoice = str_pad($next, 5, '0', STR_PAD_LEFT);

            foreach ($transfer_details as $transfer_detail) 
            {
                DB::beginTransaction();

                // Update or create branch stock
                $branchStock = Stock::where([['shop_id',$transfer_detail->from],['branch_id', $transfer_detail->to],['product_id', $transfer_detail->product_id]])->first();

                if ($branchStock) 
                {
                    $branchImeis = [];

                    // If branch already has IMEIs, append
                    if ($branchStock && $branchStock->imei) {
                        $branchImeis = explode(',', $branchStock->imei);
                    }

                    // Merge existing + new IMEIs
                    $updatedBranchImeis = array_merge($branchImeis, $transfer_detail->imei);

                    $branchStock->update([
                        'quantity'       => $branchStock->quantity + $transfer_detail->quantity,
                        'is_active'      => 1,
                        'imei' => implode(',', $updatedBranchImeis)
                    ]);

                    //Log
                    $this->addToLog($this->unique(),Auth::user()->id,'Stock Updated','App/Models/Stock','stocks',$branchStock->id,'Update',null,json_encode($transfer_detail),'Success','Stock Updated for this product');
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
                        'imei'           => implode(',', $transfer_detail->imei)
                    ]);

                    //Log
                    $this->addToLog($this->unique(),Auth::user()->id,'Stock Added','App/Models/Stock','stocks',$branchStock->id,'Insert',null,json_encode($transfer_detail),'Success','Stock Added for this product');

                }

                // Deduct from HO shop stock
                $HoStock = Stock::where([['shop_id', $transfer_detail->from],['branch_id', null],['product_id', $transfer_detail->product_id]])->first();

                
                if ($HoStock) 
                {
                    $HoImeis = [];

                    if ($HoStock && $HoStock->imei) {
                        $HoImeis = explode(',', $HoStock->imei);
                    }

                    // Remove transferred IMEIs from main shop IMEI list
                    $remainingImeis = array_diff($HoImeis, $selectedImeis);

                    $HoStock->update([
                        'quantity' => $HoStock->quantity - $transfer_detail->quantity,
                        'imei' => implode(',', $transfer_detail->imei)
                    ]);
                }

                if($transfer_detail->variation == null)
                {
                    $HoV = StockVariation::where([['stock_id',$HoStock->id],['product_id',$transfer_detail->product->id]])->first();
                    $HoV->update([
                        'quantity' => $HoV->quantity - $transfer_detail->quantity
                    ]);

                    // Find if variation already exists for this branch
                    $branchV = StockVariation::where([
                        ['stock_id', $branchStock->id],
                        ['size_id', null],
                        ['colour_id', null],
                        ['product_id', $transfer_detail->product->id],
                    ])->first();

                    if ($branchV) {
                        $branchV->update([
                            'quantity' => $branchV->quantity + $transfer_detail->quantity
                        ]);
                    } 
                    else {
                        StockVariation::create([
                            'stock_id'  => $branchStock->id,
                            'product_id'=> $transfer_detail->product->id,
                            'size_id'   => null,
                            'colour_id' => null,
                            'quantity'  => $transfer_detail->quantity,
                            'price'     => $transfer_detail->price
                        ]);
                    }
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

            }

        }

        if($transfer_detail->type == 2)
        {
            $lastInvoice = ProductHistory::where('shop_id',$transfer_detail->to)->lockForUpdate()->max('invoice');

            $next = $lastInvoice ? ((int) ltrim($lastInvoice, '0') + 1) : 1;

            $invoice = str_pad($next, 5, '0', STR_PAD_LEFT);

            foreach ($transfer_details as $transfer_detail) 
            {
                DB::beginTransaction();

                // Update Ho stock
                $HoStock = Stock::where([['shop_id',$transfer_detail->to],['branch_id', null],['product_id', $transfer_detail->product_id]])->first();

                if ($HoStock) 
                {
                    $HoImeis = [];

                    // If branch already has IMEIs, append
                    if ($HoStock && $HoStock->imei) {
                        $HoImeis = explode(',', $HoStock->imei);
                    }

                    // Merge existing + new IMEIs
                    $updatedHoImeis = array_merge($HoImeis, $transfer_detail->imei);

                    $HoStock->update([
                        'quantity'       => $HoStock->quantity + $transfer_detail->quantity,
                        'is_active'      => 1,
                        'imei' => implode(',', $updatedHoImeis)
                    ]);

                    //Log
                    $this->addToLog($this->unique(),Auth::user()->id,'Stock Updated','App/Models/Stock','stocks',$HoStock->id,'Update',null,json_encode($transfer_detail),'Success','Stock Updated for this product');
                } 

                // Deduct from branch stock
                $branchStock = Stock::where([['shop_id', $transfer_detail->to],['branch_id', $transfer_detail->from],['product_id', $transfer_detail->product_id]])->first();

                
                if ($branchStock) 
                {
                    $branchImeis = [];

                    if ($branchStock && $branchStock->imei) {
                        $branchImeis = explode(',', $branchStock->imei);
                    }

                    // Remove transferred IMEIs from main shop IMEI list
                    $remainingImeis = array_diff($branchImeis, $transfer_detail->imei);

                    $branchStock->update([
                        'quantity' => $branchStock->quantity - $transfer_detail->quantity,
                        'imei' => implode(',', $remainingImeis)
                    ]);
                }

                $transfer = ProductHistory::create([
                    'shop_id'        => $transfer_detail->to,
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

                if($transfer_detail->variation == null)
                {
                    $HoStockVariation = StockVariation::where([['stock_id',$HoStock->id],['product_id',$transfer_detail->product_id],['size_id',null],['colour_id',null]])->first();
                    if($HoStockVariation)
                    {
                        $HoStockVariation->update([
                            'quantity' => $HoStockVariation->quantity + $transfer_detail->quantity
                        ]);
                    }

                    $branchStockTransferVariation = StockVariation::where([['stock_id',$branchStock->id],['product_id',$transfer_detail->product_id],['size_id',null],['colour_id',null]])->first();
                    if($branchStockTransferVariation)
                    {
                        $branchStockTransferVariation->update([
                            'quantity' => $branchStockTransferVariation->quantity - $transfer_detail->quantity
                        ]);
                    }
                }


                //Log
                $this->addToLog($this->unique(),Auth::user()->id,'Product Transfer','App/Models/ProductHistory','product_histories',$transfer->id,'Create',null,$request,'Success','Product Transfered Successfully');

                //Notification
                $this->notification($transfer_detail->from, null,'App/Models/ProductHistory', $transfer->id, null, json_encode($transfer_detail), now(), Auth::user()->id, $transfer->product->name.' has been successfully transfered to HO '.$transfer->transfer_to->name,null, null,8);

                //Notification
                $this->notification(null, $transfer_detail->to,'App/Models/ProductHistory', $transfer->id, null, json_encode($transfer_detail), now(), Auth::user()->id, $transfer->product->name.' has been successfully transfered to HO '.$transfer->transfer_to->name,null, null,8);

                DB::commit();
            } 
        }

        if($transfer_detail->type == 3)
        {
            $lastInvoice = ProductHistory::where('shop_id',Auth::user()->parent_id)->lockForUpdate()->max('invoice');

            $next = $lastInvoice ? ((int) ltrim($lastInvoice, '0') + 1) : 1;

            $invoice = str_pad($next, 5, '0', STR_PAD_LEFT);

            foreach ($transfer_details as $transfer_detail) 
            {
                DB::beginTransaction();

                // Update transfer branch stock
                $transferBranchStock = Stock::where([['shop_id',Auth::user()->parent_id],['branch_id', $transfer_detail->to],['product_id', $transfer_detail->product_id]])->first();

                if ($transferBranchStock) 
                {
                    $transferBranchImeis = [];

                    // If branch already has IMEIs, append
                    if ($transferBranchStock && $transferBranchStock->imei) {
                        $transferBranchImeis = explode(',', $transferBranchStock->imei);
                    }

                    // Merge existing + new IMEIs
                    $updatedtransferBranchImeis = array_merge($transferBranchImeis, $transfer_detail->imei);

                    $transferBranchStock->update([
                        'quantity'       => $transferBranchStock->quantity + $transfer_detail->quantity,
                        'is_active'      => 1,
                        'imei' => implode(',', $updatedtransferBranchImeis)
                    ]);

                    //Log
                    $this->addToLog($this->unique(),Auth::user()->id,'Stock Updated','App/Models/Stock','stocks',$transferBranchStock->id,'Update',null,json_encode($transfer_detail),'Success','Stock Updated for this product');
                } 

                // Deduct from branch stock
                $branchStock = Stock::where([['shop_id', Auth::user()->parent_id],['branch_id', $transfer_detail->from],['product_id', $transfer_detail->product_id]])->first();

                
                if ($branchStock) 
                {
                    $branchImeis = [];

                    if ($branchStock && $branchStock->imei) {
                        $branchImeis = explode(',', $branchStock->imei);
                    }

                    // Remove transferred IMEIs from main shop IMEI list
                    $remainingImeis = array_diff($branchImeis, $transfer_detail->imei);

                    $branchStock->update([
                        'quantity' => $branchStock->quantity - $transfer_detail->quantity,
                        'imei' => implode(',', $remainingImeis)
                    ]);
                }

                $transfer = ProductHistory::create([
                    'shop_id'        => Auth::user()->parent_id,
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

                if($transfer_detail->variation == null)
                {
                    $transferBranchStockVariation = StockVariation::where([['stock_id',$transferBranchStock->id],['product_id',$transfer_detail->product_id],['size_id',null],['colour_id',null]])->first();
                    if($transferBranchStockVariation)
                    {
                        $transferBranchStockVariation->update([
                            'quantity' => $transferBranchStockVariation->quantity + $transfer_detail->quantity
                        ]);
                    }
                    else {
                        StockVariation::create([
                            'stock_id'  => $transferBranchStock->id,
                            'product_id'=> $transfer_detail->product->id,
                            'size_id'   => null,
                            'colour_id' => null,
                            'quantity'  => $transfer_detail->quantity,
                            'price'     => $transfer_detail->price
                        ]);
                    }

                    $branchStockTransferVariation = StockVariation::where([['stock_id',$branchStock->id],['product_id',$transfer_detail->product_id],['size_id',null],['colour_id',null]])->first();
                    if($branchStockTransferVariation)
                    {
                        $branchStockTransferVariation->update([
                            'quantity' => $branchStockTransferVariation->quantity - $transfer_detail->quantity
                        ]);
                    }
                }


                //Log
                $this->addToLog($this->unique(),Auth::user()->id,'Product Transfer','App/Models/ProductHistory','product_histories',$transfer->id,'Create',null,$request,'Success','Product Transfered Successfully');

                //Notification
                $this->notification($transfer_detail->from, null,'App/Models/ProductHistory', $transfer->id, null, json_encode($transfer_detail), now(), Auth::user()->id, $transfer->product->name.' has been successfully transfered to HO '.$transfer->transfer_to->name,null, null,8);

                //Notification
                $this->notification(null, $transfer_detail->to,'App/Models/ProductHistory', $transfer->id, null, json_encode($transfer_detail), now(), Auth::user()->id, $transfer->product->name.' has been successfully transfered to HO '.$transfer->transfer_to->name,null, null,8);

                DB::commit();
            } 
        }

        $transfer_details = QueueStock::where('unique_id',$transfer_detail->unique_id)->update(['status' => 1,'updated_on' => now(), 'updated_by' => Auth::user()->id]);
        return redirect()->back()->with('toast_success', 'Product transferred approved successfully.');

    }

    public function reject(Request $request,$company,$id)
    {
        $transfer_detail = QueueStock::where('id',$id)->first();
        $transfer_details = QueueStock::where('unique_id',$transfer_detail->unique_id)->update(['status' => 2,'updated_on' => now(), 'updated_by' => Auth::user()->id ]);
        return redirect()->back()->with('toast_success', 'Product transferred rejected successfully.');
        
    }
}
