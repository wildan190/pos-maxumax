<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index()
    {
        $products = Product::all();
        $authUser = auth()->user();
        return view('pos.index', compact('products', 'authUser'));
    }

    public function stockReport()
    {
        $products = Product::all()->map(function ($product) {
            $stockData = is_array($product->stock) ? $product->stock : json_decode($product->stock, true);

            // Ensure size_value is always an integer to prevent "string + string" errors
            $stockData = collect($stockData ?? [])->map(function ($s) {
                return [
                    'size_key' => $s['size_key'] ?? '',
                    'size_value' => (int) ($s['size_value'] ?? 0),
                ];
            })->toArray();

            $totalStock = (int) array_sum(array_column($stockData, 'size_value'));

            return [
                'id' => $product->id,
                'name' => $product->name,
                'item_code' => $product->item_code,
                'category' => $product->category ?? 'Uncategorized',
                'price' => $product->price,
                'stock' => $stockData,
                'total_stock' => $totalStock,
            ];
        });

        $grouped = $products->groupBy('category');
        $categories = $grouped->keys();

        return view('pos.stock-report', compact('products', 'grouped', 'categories'));
    }

    public function getProducts()
    {
        return response()->json(Product::all());
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array',
            'products.*.item_code' => 'nullable|string|unique:products,item_code',
            'products.*.name' => 'required|string',
            'products.*.price' => 'required|numeric',
            'products.*.type' => 'nullable|in:standard,package',
            'products.*.category' => 'nullable|string',
            'products.*.stock' => 'nullable',
            'products.*.image' => 'nullable|image|max:2048', // 2MB Max
        ]);

        $createdProducts = [];

        foreach ($request->products as $index => $productData) {
            if (empty($productData['type'])) {
                $productData['type'] = 'standard';
            }

            if (empty($productData['item_code'])) {
                // Generate a random 6 character uppercase string for item code
                $productData['item_code'] = 'ITM-' . strtoupper(Str::random(6));
            }

            $imagePath = null;
            if ($request->hasFile("products.{$index}.image")) {
                $imagePath = $request->file("products.{$index}.image")->store('products', 'public');
            }

            $productData['image'] = $imagePath;

            // Handle stock array decoding if sent as strictly string
            if (isset($productData['stock']) && is_string($productData['stock'])) {
                $productData['stock'] = json_decode($productData['stock'], true);
            }

            $createdProducts[] = Product::create($productData);
        }

        return response()->json($createdProducts);
    }

    public function updateProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'item_code' => 'required|string|unique:products,item_code,' . $product->id,
            'name' => 'required|string',
            'price' => 'required|numeric',
            'type' => 'nullable|in:standard,package',
            'category' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
        ]);

        if (empty($validated['type'])) {
            $validated['type'] = 'standard';
        }

        // Handle stock data (sent as JSON string from the form)
        if ($request->has('stock')) {
            $stockRaw = $request->input('stock');
            $validated['stock'] = is_string($stockRaw) ? json_decode($stockRaw, true) : $stockRaw;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);
        return response()->json($product);
    }

    public function destroyProduct($id)
    {
        if (auth()->user()->role !== 'superadmin') {
            return response()->json(['message' => 'Unauthorized. Only Superadmin can delete products.'], 403);
        }

        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function getTransactions()
    {
        $transactions = Transaction::with('items')->orderBy('created_at', 'desc')->get();
        return response()->json($transactions);
    }

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'subtotal' => 'required|numeric',
            'discount' => 'nullable|numeric',
            'total_amount' => 'required|numeric',
            'payment_method' => 'nullable|string',
            'items' => 'required|array',
        ]);

        $transaction = Transaction::create([
            'transaction_number' => 'T01-' . strtoupper(Str::random(6)),
            'subtotal' => $validated['subtotal'],
            'discount' => $validated['discount'] ?? 0,
            'total_amount' => $validated['total_amount'],
            'payment_method' => $validated['payment_method'] ?? 'Cash',
        ]);

        foreach ($validated['items'] as $item) {
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $item['product_id'] ?? null,
                'product_name' => $item['product_name'],
                'item_code' => $item['item_code'] ?? null,
                'size' => $item['size'] ?? null,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['subtotal'],
            ]);

            // Inventory Deduction Logic
            if (!empty($item['product_id']) && !empty($item['size'])) {
                $product = Product::find($item['product_id']);
                if ($product && !empty($product->stock)) {
                    // Always work with a plain array (model cast decodes it automatically)
                    $stockData = is_array($product->stock) ? $product->stock : json_decode($product->stock, true);

                    $updated = false;
                    // Use index-based loop to avoid dangling reference bugs from foreach &$ref
                    for ($i = 0; $i < count($stockData); $i++) {
                        if ($stockData[$i]['size_key'] === $item['size']) {
                            $stockData[$i]['size_value'] = max(0, (int) $stockData[$i]['size_value'] - (int) $item['quantity']);
                            $updated = true;
                            break;
                        }
                    }

                    if ($updated) {
                        // Force Eloquent to mark the field as dirty by setting raw JSON string
                        // This bypasses any array comparison quirks in Eloquent's dirty detection
                        $product->setRawAttributes(array_merge($product->getRawOriginal(), [
                            'stock' => json_encode($stockData)
                        ]));
                        $product->save();
                    }
                }
            }
        }

        return response()->json(['id' => $transaction->id]);
    }

    public function printReceipt($id)
    {
        $transaction = Transaction::with('items')->findOrFail($id);
        return view('pos.receipt', compact('transaction'));
    }

    public function destroyTransaction($id)
    {
        if (auth()->user()->role !== 'superadmin') {
            return response()->json(['message' => 'Unauthorized. Only Superadmin can delete transactions.'], 403);
        }

        $transaction = Transaction::findOrFail($id);
        $transaction->items()->delete();
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted successfully']);
    }
}
