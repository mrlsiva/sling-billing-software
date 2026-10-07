<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\Category;
use App\Models\Product;
use App\Models\Metric;
use App\Models\BillSetup;
use App\Models\BulkUploadLog;
use App\Models\PurchaseOrder;
use App\Models\Stock;
use App\Models\Staff;
use App\Models\StockVariation;
use App\Models\Tax;
use App\Models\Vendor;
use App\Models\User;
use App\Imports\CategoryImport;
use App\Imports\SubCategoryImport;
use App\Imports\ProductImport;
use App\Imports\AdminCatalogImport;
use App\Exports\CategoriesExport;
use App\Exports\SubCategoryExport;
use App\Exports\ProductExport;
use App\Exports\CatalogRowsExport;
use Maatwebsite\Excel\Facades\Excel;

class shopSetupController extends Controller
{
    /**
     * A shop counts as "already added" once it has any category, product, or GST rate.
     */
    private function hasSetup(int $shopId): bool
    {
        return Category::where('user_id', $shopId)->exists()
            || Product::where('user_id', $shopId)->exists()
            || Tax::where('shop_id', $shopId)->exists();
    }

    /**
     * The tax part of a GST-inclusive price. The POS uses this to split price into base and tax.
     */
    private function taxAmountFor($price, $taxId): float
    {
        $rate = (float) optional(Tax::find($taxId))->name;

        return round($price - ($price / (1 + ($rate / 100))));
    }

    private function findShop($id): User
    {
        return User::where([['role_id', 2], ['id', $id]])->firstOrFail();
    }

    public function index(Request $request)
    {
        $shops = User::where([['role_id', 2], ['is_active', 1]])->orderBy('name')->get();

        $mode = $request->get('mode') === 'new' ? 'new' : 'all';

        $visibleShops = $mode === 'new'
            ? $shops->reject(fn ($shop) => $this->hasSetup($shop->id))->values()
            : $shops;

        return view('admin.shop_setup.index', compact('visibleShops', 'mode'));
    }

    public function show(Request $request, $id)
    {
        $shop = $this->findShop($id);

        $summary = [
            'gst_rates' => Tax::where('shop_id', $shop->id)->get(),
            'gst_number' => optional($shop->user_detail)->gst,
            'categories' => Category::where('user_id', $shop->id)->count(),
            'sub_categories' => SubCategory::where('user_id', $shop->id)->count(),
            'products' => Product::where('user_id', $shop->id)->count(),
            'staff' => Staff::where('shop_id', $shop->id)->count(),
        ];

        $categories = Category::where('user_id', $shop->id)->orderBy('name')->get();
        $subCategories = SubCategory::where('user_id', $shop->id)->orderBy('name')->get();
        $metrics = Metric::where('shop_id', $shop->id)->get();

        $products = Product::where('user_id', $shop->id)
            ->when($request->filled('product'), function ($q) use ($request) {
                $s = $request->product;
                $q->where(function ($q2) use ($s) {
                    $q2->where('name', 'like', "%{$s}%")->orWhere('code', 'like', "%{$s}%");
                });
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->category))
            ->with(['category', 'sub_category', 'tax', 'metric'])
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $staff = Staff::where('shop_id', $shop->id)->orderBy('name')->get();

        $hasSetup = $this->hasSetup($shop->id);

        return view('admin.shop_setup.show', compact(
            'shop', 'summary', 'categories', 'subCategories', 'metrics', 'products', 'staff', 'hasSetup'
        ));
    }

    /**
     * Demo plan for a shop: 3 categories, 2 sub-categories each, 2 products each.
     * Names come from the shop name, e.g. "Sudha Product 1".
     */
    private function demoPlan(User $shop): array
    {
        $name = trim($shop->name);
        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3));
        $taxRates = [5, 12, 18];
        $units = ['Nos', 'Pcs', 'Kg', 'Gram', 'Ltr', 'Box'];
        $stockPerProduct = 6;

        $categories = [];
        $productNo = 0;

        for ($c = 1; $c <= 3; $c++) {
            $subCategories = [];

            for ($s = 1; $s <= 2; $s++) {
                $products = [];

                for ($p = 1; $p <= 2; $p++) {
                    $productNo++;
                    $products[] = [
                        'name' => "{$name} Product {$productNo}",
                        'code' => sprintf('%s-%03d', $code, $productNo),
                        'price' => 100 + ($productNo * 10),
                        'tax' => $taxRates[($productNo - 1) % count($taxRates)],
                        'unit' => $units[($productNo - 1) % count($units)],
                        'stock' => $stockPerProduct,
                    ];
                }

                $subCategories[] = ['name' => "{$name} Sub-category {$c}.{$s}", 'products' => $products];
            }

            $categories[] = ['name' => "{$name} Category {$c}", 'sub_categories' => $subCategories];
        }

        return [
            'taxes' => $taxRates,
            'units' => $units,
            'vendor' => "{$name} Demo Vendor",
            'invoice' => "{$code}-DEMO-001",
            'staff' => ["{$name} Staff 1", "{$name} Staff 2"],
            'bill_prefix' => $this->billPrefixFor($name),
            'categories' => $categories,
        ];
    }

    /**
     * Bill prefix in the same format as existing shops, e.g. INV-CAPZ-ADD-01-26-27-0.
     * The financial year runs April to March.
     */
    private function billPrefixFor(string $shopName): string
    {
        $shortName = strtoupper(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $shopName), '-'));
        $startYear = now()->month >= 4 ? now()->year : now()->year - 1;
        $financialYear = sprintf('%02d-%02d', $startYear % 100, ($startYear + 1) % 100);

        return "INV-{$shortName}-{$financialYear}-0";
    }

    /**
     * Shows what the demo setup will add. Nothing is written here.
     */
    public function previewDemo($id)
    {
        $shop = $this->findShop($id);

        if ($this->hasSetup($shop->id)) {
            return redirect()->route('admin.shop_setup.show', ['id' => $shop->id])
                ->with('toast_error', 'This shop already has setup data. Demo setup was not loaded.');
        }

        $plan = $this->currentDemoDraft($shop);

        return view('admin.shop_setup.demo_preview', compact('shop', 'plan'));
    }

    private function demoDraftKey(User $shop): string
    {
        return 'shop_setup_demo_draft.'.$shop->id;
    }

    /**
     * The editable demo draft kept in the session until it is confirmed or reset.
     */
    private function currentDemoDraft(User $shop): array
    {
        $key = $this->demoDraftKey($shop);

        if (!session()->has($key)) {
            session()->put($key, $this->demoPlan($shop));
        }

        return session()->get($key);
    }

    /**
     * Edit, add, remove or reset items in the demo draft. Nothing is written to the database.
     */
    public function demoDraftAction(Request $request, $id)
    {
        $shop = $this->findShop($id);

        if ($this->hasSetup($shop->id)) {
            return redirect()->route('admin.shop_setup.show', ['id' => $shop->id])
                ->with('toast_error', 'This shop already has setup data. Demo setup was not loaded.');
        }

        $plan = $this->currentDemoDraft($shop);
        $action = $request->input('action');

        if ($action === 'reset') {
            $plan = $this->demoPlan($shop);
        } elseif ($action === 'add_staff') {
            $data = $request->validate(['name' => 'required|string|max:50']);
            abort_if(in_array($data['name'], $plan['staff'], true), 422, 'This staff name is already in the demo.');
            $plan['staff'][] = $data['name'];
        } elseif ($action === 'delete_staff') {
            $data = $request->validate(['i' => 'required|integer']);
            abort_unless(isset($plan['staff'][$data['i']]), 404, 'That staff is no longer in the demo preview. Refresh the page.');
            array_splice($plan['staff'], $data['i'], 1);
        } else {
            abort(400, 'Unknown demo action.');
        }

        session()->put($this->demoDraftKey($shop), $plan);

        return redirect()->route('admin.shop_setup.demo_preview', ['id' => $shop->id])
            ->with('toast_success', $action === 'reset' ? 'Demo preview reset.' : 'Demo preview updated.');
    }

    /**
     * The categories/sub-categories/products part of the draft, flattened to
     * one row per sub-category: Category | Sub Category | Products (comma list).
     */
    private function catalogRows(array $plan): array
    {
        $rows = [];

        foreach ($plan['categories'] as $category) {
            foreach ($category['sub_categories'] as $sub) {
                $rows[] = [
                    'category' => $category['name'],
                    'sub_category' => $sub['name'],
                    'products' => collect($sub['products'])->pluck('name')->implode(', '),
                ];
            }
        }

        return $rows;
    }

    public function demoCatalogTemplate($id)
    {
        $shop = $this->findShop($id);
        $name = trim($shop->name);

        $rows = [
            ['category' => "{$name} Category 1", 'sub_category' => 'Electrical Parts', 'products' => 'Capacitor, Contactor'],
            ['category' => "{$name} Category 1", 'sub_category' => 'Mechanical Parts', 'products' => 'Fan Blade, Service Valve'],
        ];

        return Excel::download(new CatalogRowsExport($rows), 'Catalog_Template.xlsx');
    }

    public function demoCatalogCurrent($id)
    {
        $shop = $this->findShop($id);
        $plan = $this->currentDemoDraft($shop);

        return Excel::download(new CatalogRowsExport($this->catalogRows($plan)), $shop->slug_name.'_Catalog_Draft.xlsx');
    }

    /**
     * Replaces the draft's categories, sub-categories and products with what's in the
     * uploaded file. A product already in the draft under the same category and
     * sub-category keeps its existing code, price, GST and unit; a new product name
     * gets those assigned automatically, the same way the generated demo does.
     */
    public function demoCatalogImport(Request $request, $id)
    {
        $shop = $this->findShop($id);

        if ($this->hasSetup($shop->id)) {
            return redirect()->route('admin.shop_setup.show', ['id' => $shop->id])
                ->with('toast_error', 'This shop already has setup data. Demo setup was not loaded.');
        }

        $request->validate(['file' => 'required|mimes:xlsx|max:10000']);

        $plan = $this->currentDemoDraft($shop);

        $import = new AdminCatalogImport();
        Excel::import($import, $request->file('file'));

        // Index existing products by category + sub-category + name so re-uploading
        // an edited file doesn't reset fields on products that didn't change.
        $existingProducts = [];
        $maxProductNo = 0;
        foreach ($plan['categories'] as $category) {
            foreach ($category['sub_categories'] as $sub) {
                foreach ($sub['products'] as $product) {
                    $key = strtolower($category['name']).'|'.strtolower($sub['name']).'|'.strtolower($product['name']);
                    $existingProducts[$key] = $product;
                    if (preg_match('/-(\d+)$/', $product['code'], $m)) {
                        $maxProductNo = max($maxProductNo, (int) $m[1]);
                    }
                }
            }
        }

        $taxRates = $plan['taxes'];
        $units = $plan['units'];
        $codePrefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $shop->name), 0, 3));
        $productNo = $maxProductNo;

        $newCategories = [];
        $skipped = [];

        foreach ($import->rows as $i => $row) {
            $rowNo = $i + 2; // heading row is row 1

            $categoryName = trim($row['category'] ?? '');
            $subCategoryName = trim($row['sub_category'] ?? '');
            $productsCell = trim($row['products'] ?? '');

            if ($categoryName === '' || $subCategoryName === '' || $productsCell === '') {
                $skipped[] = "Row {$rowNo}: category, sub category and products are all required.";
                continue;
            }

            $productNames = array_values(array_filter(array_map('trim', explode(',', $productsCell))));
            if (empty($productNames)) {
                $skipped[] = "Row {$rowNo}: no product names found in the products column.";
                continue;
            }

            $products = [];
            foreach ($productNames as $name) {
                $key = strtolower($categoryName).'|'.strtolower($subCategoryName).'|'.strtolower($name);

                if (isset($existingProducts[$key])) {
                    $products[] = array_merge($existingProducts[$key], ['name' => $name]);
                    continue;
                }

                $productNo++;
                $products[] = [
                    'name' => $name,
                    'code' => sprintf('%s-%03d', $codePrefix, $productNo),
                    'price' => 100 + ($productNo * 10),
                    'tax' => $taxRates[($productNo - 1) % count($taxRates)],
                    'unit' => $units[($productNo - 1) % count($units)],
                    'stock' => 6,
                ];
            }

            $catIndex = null;
            foreach ($newCategories as $idx => $existingCategory) {
                if (strcasecmp($existingCategory['name'], $categoryName) === 0) {
                    $catIndex = $idx;
                    break;
                }
            }
            if ($catIndex === null) {
                $newCategories[] = ['name' => $categoryName, 'sub_categories' => []];
                $catIndex = count($newCategories) - 1;
            }

            $subIndex = null;
            foreach ($newCategories[$catIndex]['sub_categories'] as $idx => $existingSub) {
                if (strcasecmp($existingSub['name'], $subCategoryName) === 0) {
                    $subIndex = $idx;
                    break;
                }
            }
            if ($subIndex === null) {
                $newCategories[$catIndex]['sub_categories'][] = ['name' => $subCategoryName, 'products' => []];
                $subIndex = count($newCategories[$catIndex]['sub_categories']) - 1;
            }

            // A category/sub-category pair repeated on another row adds to the same sub-category.
            $newCategories[$catIndex]['sub_categories'][$subIndex]['products'] = array_merge(
                $newCategories[$catIndex]['sub_categories'][$subIndex]['products'],
                $products
            );
        }

        if (empty($newCategories)) {
            return redirect()->route('admin.shop_setup.demo_preview', ['id' => $shop->id])
                ->with('toast_error', 'Nothing could be imported. '.implode(' | ', $skipped));
        }

        $plan['categories'] = $newCategories;
        session()->put($this->demoDraftKey($shop), $plan);

        $productCount = collect($newCategories)->sum(fn ($c) => collect($c['sub_categories'])->sum(fn ($s) => count($s['products'])));
        $message = "Catalog updated: ".count($newCategories)." categories, {$productCount} products.";

        if (!empty($skipped)) {
            return redirect()->route('admin.shop_setup.demo_preview', ['id' => $shop->id])
                ->with('toast_error', $message.' Skipped: '.implode(' | ', $skipped));
        }

        return redirect()->route('admin.shop_setup.demo_preview', ['id' => $shop->id])
            ->with('toast_success', $message);
    }

    /**
     * Adds the demo setup after the preview is confirmed.
     */
    public function loadDemo($id)
    {
        $shop = $this->findShop($id);

        if ($this->hasSetup($shop->id)) {
            return redirect()->route('admin.shop_setup.show', ['id' => $shop->id])
                ->with('toast_error', 'This shop already has setup data. Demo setup was not loaded.');
        }

        $plan = $this->currentDemoDraft($shop);

        if (collect($plan['categories'])->isEmpty()) {
            return redirect()->route('admin.shop_setup.demo_preview', ['id' => $shop->id])
                ->with('toast_error', 'Add at least one category before confirming.');
        }

        DB::transaction(function () use ($shop, $plan) {
            $taxes = collect($plan['taxes'])->mapWithKeys(fn ($rate) => [
                $rate => Tax::firstOrCreate(['shop_id' => $shop->id, 'name' => $rate], ['is_active' => 1]),
            ]);

            $metrics = collect($plan['units'])->mapWithKeys(fn ($name) => [
                $name => Metric::firstOrCreate(['shop_id' => $shop->id, 'name' => $name], ['is_active' => 1]),
            ]);

            $vendor = Vendor::firstOrCreate(
                ['shop_id' => $shop->id, 'name' => $plan['vendor']],
                ['phone' => '0000000000', 'is_active' => 1]
            );

            foreach ($plan['staff'] as $staffName) {
                Staff::create(['shop_id' => $shop->id, 'name' => $staffName, 'is_active' => 1]);
            }

            BillSetup::create([
                'shop_id' => $shop->id,
                'branch_id' => null,
                'bill_number' => $plan['bill_prefix'],
                'setup_on' => now(),
                'is_active' => 1,
            ]);

            foreach ($plan['categories'] as $categoryPlan) {
                $category = Category::create(['user_id' => $shop->id, 'name' => $categoryPlan['name'], 'is_active' => 1]);

                foreach ($categoryPlan['sub_categories'] as $subPlan) {
                    $subCategory = SubCategory::create([
                        'user_id' => $shop->id,
                        'category_id' => $category->id,
                        'name' => $subPlan['name'],
                        'is_active' => 1,
                    ]);

                    foreach ($subPlan['products'] as $item) {
                        $product = Product::create([
                            'user_id' => $shop->id,
                            'category_id' => $category->id,
                            'sub_category_id' => $subCategory->id,
                            'name' => $item['name'],
                            'code' => $item['code'],
                            'price' => $item['price'],
                            'tax_amount' => round($item['price'] - ($item['price'] / (1 + ($item['tax'] / 100)))),
                            'tax_id' => $taxes[$item['tax']]->id,
                            'metric_id' => $metrics[$item['unit']]->id,
                            'quantity' => $item['stock'],
                            'is_active' => 1,
                        ]);

                        $stock = Stock::create([
                            'shop_id' => $shop->id,
                            'branch_id' => null,
                            'category_id' => $category->id,
                            'sub_category_id' => $subCategory->id,
                            'product_id' => $product->id,
                            'quantity' => $item['stock'],
                        ]);

                        // The POS sells from this default variation (no size or colour), as product creation does.
                        StockVariation::create([
                            'stock_id' => $stock->id,
                            'product_id' => $product->id,
                            'quantity' => $item['stock'],
                            'price' => (int) round($item['price']),
                        ]);

                        $net = $item['price'] * $item['stock'];

                        PurchaseOrder::create([
                            'shop_id' => $shop->id,
                            'vendor_id' => $vendor->id,
                            'payment_id' => 1,
                            'invoice_no' => $plan['invoice'],
                            'invoice_date' => now()->toDateString(),
                            'category_id' => $category->id,
                            'sub_category_id' => $subCategory->id,
                            'product_id' => $product->id,
                            'metric_id' => $metrics[$item['unit']]->id,
                            'quantity' => $item['stock'],
                            'price_per_unit' => $item['price'],
                            'tax' => $item['tax'],
                            'discount' => 0,
                            'net_cost' => $net,
                            'gross_cost' => round($net + ($net * $item['tax'] / 100), 2),
                            'status' => 0,
                        ]);
                    }
                }
            }
        });

        session()->forget($this->demoDraftKey($shop));

        return redirect()->route('admin.shop_setup.show', ['id' => $shop->id])
            ->with('toast_success', 'Demo setup added: categories, sub-categories, products, GST rates, units, a demo purchase and stock.');
    }

    public function storeTax(Request $request, $id)
    {
        $shop = $this->findShop($id);

        $request->validate([
            'name' => ['required', 'numeric', 'min:0', 'max:100',
                Rule::unique('taxes', 'name')->where(fn ($q) => $q->where('shop_id', $shop->id)),
            ],
        ], ['name.required' => 'GST rate is required.', 'name.unique' => 'This GST rate already exists.']);

        Tax::create(['shop_id' => $shop->id, 'name' => $request->name, 'is_active' => 1]);

        return redirect()->back()->with('toast_success', 'GST rate added.');
    }

    public function storeCategory(Request $request, $id)
    {
        $shop = $this->findShop($id);

        $request->validate([
            'name' => ['required', 'string', 'max:50',
                Rule::unique('categories', 'name')->where(fn ($q) => $q->where('user_id', $shop->id)),
            ],
        ], ['name.required' => 'Category name is required.', 'name.unique' => 'This category already exists.']);

        Category::create(['user_id' => $shop->id, 'name' => $request->name, 'is_active' => 1]);

        return redirect()->back()->with('toast_success', 'Category added.');
    }

    public function storeSubCategory(Request $request, $id)
    {
        $shop = $this->findShop($id);

        $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where(fn ($q) => $q->where('user_id', $shop->id))],
            'name' => ['required', 'string', 'max:50',
                Rule::unique('sub_categories', 'name')->where(fn ($q) => $q->where('user_id', $shop->id)->where('category_id', $request->category_id)),
            ],
        ], ['category_id.required' => 'Pick a category first.', 'name.unique' => 'This sub-category already exists in that category.']);

        SubCategory::create([
            'user_id' => $shop->id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'is_active' => 1,
        ]);

        return redirect()->back()->with('toast_success', 'Sub-category added.');
    }

    public function storeProduct(Request $request, $id)
    {
        $shop = $this->findShop($id);

        $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where(fn ($q) => $q->where('user_id', $shop->id))],
            'sub_category_id' => ['required', Rule::exists('sub_categories', 'id')->where(fn ($q) => $q->where('user_id', $shop->id))],
            'name' => ['required', 'string', 'max:100',
                Rule::unique('products')->where(fn ($q) => $q->where('user_id', $shop->id)
                    ->where('category_id', $request->category_id)
                    ->where('sub_category_id', $request->sub_category_id)),
            ],
            'code' => ['required', 'string', 'max:50',
                Rule::unique('products')->where(fn ($q) => $q->where('user_id', $shop->id)),
            ],
            'hsn_code' => 'nullable|string|max:50',
            'price' => 'required|numeric|min:1',
            'tax_id' => ['required', Rule::exists('taxes', 'id')->where(fn ($q) => $q->where('shop_id', $shop->id))],
            'metric_id' => ['required', Rule::exists('metrics', 'id')->where(fn ($q) => $q->where('shop_id', $shop->id))],
        ], [
            'category_id.required' => 'Category is required.',
            'sub_category_id.required' => 'Sub-category is required.',
            'tax_id.required' => 'Add a GST rate for this shop first.',
            'metric_id.required' => 'Add a unit for this shop first.',
            'code.unique' => 'This product code is already used by this shop.',
        ]);

        Product::create([
            'user_id' => $shop->id,
            'category_id' => $request->category_id,
            'sub_category_id' => $request->sub_category_id,
            'name' => $request->name,
            'code' => $request->code,
            'hsn_code' => $request->hsn_code,
            'price' => $request->price,
            'tax_amount' => $this->taxAmountFor($request->price, $request->tax_id),
            'tax_id' => $request->tax_id,
            'metric_id' => $request->metric_id,
            'quantity' => 0,
            'is_active' => 1,
        ]);

        return redirect()->back()->with('toast_success', 'Product added. Opening stock is not set here.');
    }

    public function storeStaff(Request $request, $id)
    {
        $shop = $this->findShop($id);

        $request->validate([
            'name' => ['required', 'string', 'max:50',
                Rule::unique('staffs')->where(fn ($q) => $q->where('shop_id', $shop->id)),
            ],
            'phone' => 'nullable|numeric|digits:10',
        ], ['name.required' => 'Staff name is required.', 'name.unique' => 'This staff name already exists in this shop.']);

        Staff::create([
            'shop_id' => $shop->id,
            'name' => $request->name,
            'phone' => $request->phone,
            'is_active' => 1,
        ]);

        return redirect()->back()->with('toast_success', 'Staff added.');
    }

    // ---------------------------------------------------------------
    // Export / Import — categories, sub-categories and products for
    // the chosen shop. Reuses the same Export/Import classes and the
    // same bulk-upload-log pattern as the shop owner's own panel.
    // ---------------------------------------------------------------

    public function exportCategories($id)
    {
        $shop = $this->findShop($id);

        $categories = Category::with('sub_categories')
            ->where('user_id', $shop->id)
            ->orderByDesc('id')
            ->get();

        return Excel::download(new CategoriesExport($categories), $shop->slug_name.'_Categories_'.now()->format('d-m-Y_h-i_A').'.xlsx');
    }

    public function exportSubCategories($id)
    {
        $shop = $this->findShop($id);

        $subCategories = SubCategory::with('category')
            ->where('user_id', $shop->id)
            ->orderByDesc('id')
            ->get();

        return Excel::download(new SubCategoryExport($subCategories), $shop->slug_name.'_SubCategories_'.now()->format('d-m-Y_h-i_A').'.xlsx');
    }

    public function exportProducts($id)
    {
        $shop = $this->findShop($id);

        $query = Product::with(['category', 'sub_category', 'metric', 'tax'])
            ->where('user_id', $shop->id)
            ->orderByDesc('id');

        return Excel::download(new ProductExport($query), $shop->slug_name.'_Products_'.now()->format('d-m-Y_h-i_A').'.xlsx');
    }

    /**
     * One run_id per upload, a saved copy of the file and a text log, recorded against
     * the shop (not the admin), so it shows up in that shop's own bulk-upload history too.
     */
    private function runBulkImport($shop, Request $request, string $module, string $directoryPrefix, $import): array
    {
        $request->validate(['file' => 'required|mimes:xlsx|max:10000']);

        do {
            $runId = rand(100000, 999999);
        } while (BulkUploadLog::where('run_id', $runId)->exists());

        $importer = $import($runId);
        Excel::import($importer, $request->file('file'));

        $skipped = [];
        if ($importer->failures()->isNotEmpty()) {
            foreach ($importer->failures() as $failure) {
                $skipped[] = "Row {$failure->row()}: ".implode(', ', $failure->errors());
            }
        }

        $totalRecords = $importer->getRowCount();
        $errorRecords = count($skipped);
        $successfulRecords = max(0, $totalRecords - $errorRecords);

        $directory = "bulk_uploads/{$directoryPrefix}/{$runId}";
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $excelPath = $uploadedFile->storeAs($directory, $originalName, 'public');

        $logContent = "======================".PHP_EOL
            ."Bulk Upload Report (via Admin Shop Setup)".PHP_EOL
            ."Shop: {$shop->name} (#{$shop->id})".PHP_EOL
            ."Uploaded On: ".now().PHP_EOL
            ."Run ID: {$runId}".PHP_EOL
            ."Uploaded File: {$originalName}".PHP_EOL
            ."Total Records: {$totalRecords}".PHP_EOL
            ."Successful Records: {$successfulRecords}".PHP_EOL
            ."Error Records: {$errorRecords}".PHP_EOL;

        if ($errorRecords > 0) {
            $logContent .= "Error Details:".PHP_EOL;
            foreach ($skipped as $error) {
                $logContent .= "- {$error}".PHP_EOL;
            }
        }
        $logContent .= "======================".PHP_EOL;

        $logFile = "{$directory}/log.txt";
        Storage::disk('public')->put($logFile, $logContent);

        BulkUploadLog::create([
            'user_id' => $shop->id,
            'run_id' => $runId,
            'run_on' => now(),
            'module' => $module,
            'total_record' => $totalRecords,
            'successfull_record' => $successfulRecords,
            'error_record' => $errorRecords,
            'excel' => $excelPath,
            'log' => $logFile,
        ]);

        return [$successfulRecords, $errorRecords, $skipped];
    }

    public function importCategories(Request $request, $id)
    {
        $shop = $this->findShop($id);

        [$ok, $errors, $skipped] = $this->runBulkImport($shop, $request, 'Category', 'categories',
            fn ($runId) => new CategoryImport($runId, $shop->id));

        if ($errors > 0) {
            return redirect()->route('admin.shop_setup.show', ['id' => $shop->id])
                ->with('toast_error', "Categories imported: {$ok}. Skipped: ".implode(' | ', $skipped));
        }

        return redirect()->route('admin.shop_setup.show', ['id' => $shop->id])
            ->with('toast_success', "Categories imported: {$ok}.");
    }

    public function importSubCategories(Request $request, $id)
    {
        $shop = $this->findShop($id);

        [$ok, $errors, $skipped] = $this->runBulkImport($shop, $request, 'SubCategory', 'sub_categories',
            fn ($runId) => new SubCategoryImport($runId, $shop->id));

        if ($errors > 0) {
            return redirect()->route('admin.shop_setup.show', ['id' => $shop->id])
                ->with('toast_error', "Sub-categories imported: {$ok}. Skipped: ".implode(' | ', $skipped));
        }

        return redirect()->route('admin.shop_setup.show', ['id' => $shop->id])
            ->with('toast_success', "Sub-categories imported: {$ok}.");
    }

    public function importProducts(Request $request, $id)
    {
        $shop = $this->findShop($id);

        [$ok, $errors, $skipped] = $this->runBulkImport($shop, $request, 'Product', 'products',
            fn ($runId) => new ProductImport($runId, $shop->id));

        if ($errors > 0) {
            return redirect()->route('admin.shop_setup.show', ['id' => $shop->id])
                ->with('toast_error', "Products imported: {$ok}. Skipped: ".implode(' | ', $skipped));
        }

        return redirect()->route('admin.shop_setup.show', ['id' => $shop->id])
            ->with('toast_success', "Products imported: {$ok}.");
    }
}
