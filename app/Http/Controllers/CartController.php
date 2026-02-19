<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LukePOLO\LaraCart\Facades\LaraCart;

class CartController extends Controller
{
    /**
     * Show cart page
     */
    public function index()
    {
        $cart = LaraCart::get();
        $items = LaraCart::getItems();
        $total = LaraCart::total(false);
        $subTotal = LaraCart::subTotal(false);
        $tax = LaraCart::taxTotal(false);

        return view('cart.index', compact('cart', 'items', 'total', 'subTotal', 'tax'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
        ]);

        $product = \App\Models\Product::findOrFail($request->product_id);

        // Options - solo datos primitivos, sin relaciones dinámicas
        $options = [
            'sku' => $product->sku,
            'slug' => $product->slug,
            'brand' => $product->brand?->name ?? null,
            'list_price' => $product->list_price,
        ];

        // LaraCart por defecto incrementa la cantidad si el item ya existe
        LaraCart::add(
            $product->id,      // itemID
            $product->name,    // name
            $request->qty,     // qty
            $product->price,   // price
            $options,          // options
            true,              // taxable
            false              // lineItem
        );

        return redirect()->back()->with('success', 'Producto agregado al carrito');
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request)
    {
        $request->validate([
            'item_id' => 'required',
            'qty' => 'required|integer|min:1',
        ]);

        // LaraCart::updateItem($itemHash, $key, $value)
        LaraCart::updateItem($request->item_id, 'qty', $request->qty);

        return redirect()->back()->with('success', 'Carrito actualizado');
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request)
    {
        $request->validate([
            'item_id' => 'required',
        ]);

        LaraCart::removeItem($request->item_id);

        return redirect()->back()->with('success', 'Producto eliminado del carrito');
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        LaraCart::emptyCart();

        return redirect()->route('cart.index')->with('success', 'Carrito vaciado');
    }

    /**
     * Checkout page
     */
    public function checkout()
    {
        $items = LaraCart::getItems();

        if (empty($items)) {
            return redirect()->route('cart.index')->with('error', 'El carrito está vacío');
        }

        $total = LaraCart::total(false);
        $subTotal = LaraCart::subTotal(false);
        $tax = LaraCart::taxTotal(false);

        return view('cart.checkout', compact('items', 'total', 'subTotal', 'tax'));
    }

    /**
     * Process order and generate WhatsApp message
     */
    public function processOrder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $items = LaraCart::getItems();

        if (empty($items)) {
            return redirect()->route('cart.index')->with('error', 'El carrito está vacío');
        }

        // Build WhatsApp message
        $message = "🛒 *Nuevo Pedido - Target Eyewear*\n\n";
        $message .= "👤 *Cliente:* {$request->name}\n";
        $message .= "📱 *Teléfono:* {$request->phone}\n";
        $message .= "📍 *Dirección:* {$request->address}\n";

        if ($request->notes) {
            $message .= "📝 *Notas:* {$request->notes}\n";
        }

        $message .= "\n📦 *Productos:*\n";
        $message .= str_repeat('-', 40) . "\n";

        $total = 0;
        foreach ($items as $item) {
            $subtotal = $item->qty * $item->price;
            $total += $subtotal;

            $message .= "• {$item->name}\n";
            $message .= "  Cantidad: {$item->qty}\n";
            $message .= "  Precio unitario: Gs. " . number_format($item->price, 0, ',', '.') . "\n";
            $message .= "  Subtotal: Gs. " . number_format($subtotal, 0, ',', '.') . "\n\n";
        }

        $message .= str_repeat('-', 40) . "\n";
        $message .= "*TOTAL: Gs. " . number_format($total, 0, ',', '.') . "*\n";

        // Encode message for URL
        $encodedMessage = urlencode($message);
        $whatsappUrl = "https://wa.me/?text={$encodedMessage}";

        // Clear cart after generating order
        LaraCart::emptyCart();

        return redirect($whatsappUrl);
    }

    /**
     * Unified API endpoint for cart operations
     */
    public function api(Request $request)
    {
        $action = $request->input('action');

        try {
            switch ($action) {
                case 'add':
                    $request->validate([
                        'product_id' => 'required|exists:products,id',
                        'qty' => 'required|integer|min:1',
                    ]);

                    $product = \App\Models\Product::findOrFail($request->product_id);

                    $options = [
                        'sku' => $product->sku,
                        'slug' => $product->slug,
                        'brand' => $product->brand?->name ?? null,
                        'list_price' => $product->list_price,
                    ];

                    LaraCart::add(
                        $product->id,
                        $product->name,
                        $request->qty,
                        $product->price,
                        $options,
                        true,
                        false
                    );

                    return response()->json([
                        'success' => true,
                        'message' => 'Producto agregado al carrito',
                        'count' => LaraCart::count(),
                    ]);

                case 'update':
                    $request->validate([
                        'item_id' => 'required',
                        'qty' => 'required|integer|min:1',
                    ]);

                    LaraCart::updateItem($request->item_id, 'qty', $request->qty);

                    return $this->getCartData('Carrito actualizado');

                case 'remove':
                    $request->validate([
                        'item_id' => 'required',
                    ]);

                    LaraCart::removeItem($request->item_id);

                    return $this->getCartData('Producto eliminado del carrito');

                case 'clear':
                    LaraCart::emptyCart();

                    return response()->json([
                        'success' => true,
                        'message' => 'Carrito vaciado',
                        'count' => 0,
                    ]);

                case 'get':
                    return $this->getCartData();

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Acción no válida',
                    ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Helper to get cart data
     */
    private function getCartData($message = null)
    {
        $items = LaraCart::getItems();
        $total = LaraCart::total(false);
        $subTotal = LaraCart::subTotal(false);
        $tax = LaraCart::taxTotal(false);

        $itemsArray = [];
        $totalCount = 0;

        foreach ($items as $item) {
            // Get product data to ensure we have all information
            $productInCart = \App\Models\Product::find($item->id);
            $imagePath = $productInCart && $productInCart->mainImage ? $productInCart->mainImage->file_url : null;

            // Get options from item or fallback to product
            $itemOptions = $item->options ?? [];
            $sku = $itemOptions['sku'] ?? ($productInCart->sku ?? null);
            $slug = $itemOptions['slug'] ?? ($productInCart->slug ?? null);
            $brand = $itemOptions['brand'] ?? ($productInCart->brand?->name ?? null);
            $listPrice = $itemOptions['list_price'] ?? ($productInCart->list_price ?? null);

            // Convert qty to integer
            $qty = (int) $item->qty;
            $totalCount += $qty;

            $itemsArray[] = [
                'hash' => $item->getHash(),
                'id' => $item->id,
                'name' => $item->name,
                'qty' => $qty,
                'price' => $item->price,
                'subtotal' => $qty * $item->price,
                'image' => $imagePath,
                'options' => [
                    'sku' => $sku,
                    'slug' => $slug,
                    'brand' => $brand,
                    'list_price' => $listPrice,
                ],
            ];
        }

        $response = [
            'success' => true,
            'items' => $itemsArray,
            'count' => $totalCount,
            'total' => $total,
            'subTotal' => $subTotal,
            'tax' => $tax,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response);
    }
}
