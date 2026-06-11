<?php

use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\EmprendedorController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\LocalesExternosController;
use App\Http\Controllers\LocalesInternosController;
use App\Http\Controllers\PedidoController;
use Illuminate\Support\Facades\Route;

// Google OAuth (must be web, not API — needs session state)
Route::get('/auth/google',          [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

// Auth (público)
Route::middleware('guest')->group(function () {
    Route::get('/login', [MagicLinkController::class, 'showLogin'])->name('login');
    Route::post('/login', [MagicLinkController::class, 'sendLink'])->name('login.send');
});
Route::get('/auth/verify/{token}', [MagicLinkController::class, 'verifyLink'])->name('auth.verify');
Route::post('/logout', [MagicLinkController::class, 'logout'])->name('logout')->middleware('auth');

// Rutas autenticadas
Route::middleware('auth')->group(function () {

    // Redirigir raíz a locales externos
    Route::redirect('/', '/locales-externos');

    // Locales Externos
    Route::get('/locales-externos', [LocalesExternosController::class, 'index'])->name('locales.externos');
    Route::get('/locales-externos/{local}', [LocalesExternosController::class, 'show'])->name('locales.externos.show');

    // Locales Internos
    Route::get('/locales-internos', [LocalesInternosController::class, 'index'])->name('locales.internos');
    Route::get('/locales-internos/{local}', [LocalesInternosController::class, 'show'])->name('locales.internos.show');

    // Pedidos
    Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store');
    Route::post('/pedidos/{pedido}/calificar', [PedidoController::class, 'calificar'])->name('pedidos.calificar');

    // Panel del Emprendedor
    Route::prefix('emprendedor')->name('emprendedor.')->group(function () {
        Route::get('/', [EmprendedorController::class, 'dashboard'])->name('dashboard');
        Route::post('/registro', [EmprendedorController::class, 'registrar'])->name('registrar');
        Route::put('/local', [EmprendedorController::class, 'actualizarLocal'])->name('local.update');
        Route::post('/productos', [EmprendedorController::class, 'guardarProducto'])->name('productos.store');
        Route::post('/pedidos/{pedido}/confirmar', [EmprendedorController::class, 'confirmarPedido'])->name('pedidos.confirmar');
        Route::post('/pedidos/{pedido}/listo', [EmprendedorController::class, 'marcarListo'])->name('pedidos.listo');
    });
});
