<?php

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\InvoiceItemController;

// Rutas para Items (catálogo de productos/servicios)
Route::middleware(['auth'])->group(function () {
    // Rutas CRUD para Items
    Route::resource('items', ItemController::class);

    // Rutas para InvoiceItems (líneas de factura)
    Route::post('invoices/{invoice}/items', [InvoiceItemController::class, 'store'])
        ->name('invoice-items.store');

    Route::put('invoices/{invoice}/items/{item}', [InvoiceItemController::class, 'update'])
        ->name('invoice-items.update');

    Route::delete('invoices/{invoice}/items/{item}', [InvoiceItemController::class, 'destroy'])
        ->name('invoice-items.destroy');

    // API para autocompletar items al crear facturas
    Route::get('/api/items/search', function (Request $request) {
        $query = $request->input('query');
        $items = Item::where('user_id', Auth::id())
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%$query%")
                    ->orWhere('description', 'like', "%$query%")
                    ->orWhere('sku', 'like', "%$query%");
            })
            ->where('is_active', true)
            ->limit(10)
            ->get(['id', 'name', 'description', 'price', 'tax_percent']);

        return response()->json($items);
    })->name('api.items.search');
});
