<?php

namespace App\Http\Controllers\branches;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Stock;
use App\Models\Product;

class stockController extends Controller
{
    public function index(Request $request)
    {
        $stocks = Stock::where('branch_id', Auth::user()->id)
        ->when(request('product'), function ($query) {
            $search = request('product');
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($q1) use ($search) {
                    $q1->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('product.category', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('product.sub_category', function ($q3) use ($search) {
                    $q3->where('name', 'like', "%{$search}%");
                });
            });
        })->orderBy('category_id')->orderBy('sub_category_id')->orderBy('product_id')->paginate(10);

        return view('branches.products.index',compact('stocks'));
    }

    public function qrcode(Request $request,$company,Product $product)
    {
        // $product = Product::where('id',$id)->first();
        // return view('branches.products.qrcode',compact('product'));

        $userName = Auth::user()->name;

        $tspl = "
        SIZE 60 mm,40 mm
        GAP 2 mm,0
        CLS

        // Shop Name
        TEXT 20,20,\"3\",0,1,1,\"{$userName}\"

        // Product Code
        TEXT 20,50,\"3\",0,1,1,\"{$product->code}\"

        // QR Code on Left
        QRCODE 20,80,L,5,A,0,\"{$product->id}\"

        // Product Name on Right of QR
        TEXT 140,80,\"3\",0,1,1,\"{$product->name}\"

        // Price below product name
        TEXT 140,120,\"3\",0,1,1,\"Rs. " . number_format($product->price, 2) . "\"

        PRINT 5
        ";

        // Save file
        $filename = storage_path("app/label.tspl");
        file_put_contents($filename, $tspl);

        // Send to printer (network example)
        $printerIp = "192.1688.1.41"; // printer IP
        $port = 9100;
        $fp = fsockopen($printerIp, $port);
        if ($fp) {
            fwrite($fp, $tspl);
            fclose($fp);
        }

        return redirect()->back()->with('toast_success', 'Label sent to printer!');

        //return back()->with('success', 'Label sent to printer!');

    }

    public function barcode(Request $request,$company,$id)
    {
        $product = Product::where('id',$id)->first();
        return view('branches.products.barcode',compact('product'));
    }

}
