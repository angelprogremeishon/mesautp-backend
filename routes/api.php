<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmprendedorController;
use App\Http\Controllers\Api\LocalesController;
use App\Http\Controllers\Api\PedidoController;
use Illuminate\Support\Facades\Route;

// ── Auth (público) ────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('send-link', [AuthController::class, 'sendLink']);
    Route::post('verify',    [AuthController::class, 'verifyToken']);
});

// ── Locales (público) ─────────────────────────────────────────────────────────
Route::get('locales/externos',       [LocalesController::class, 'externos']);
Route::get('locales/externos/{local}', [LocalesController::class, 'externoShow']);
Route::get('locales/internos',       [LocalesController::class, 'internos']);
Route::get('locales/internos/{local}', [LocalesController::class, 'internoShow']);

// ── Autenticado ───────────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me',      [AuthController::class, 'me']);

    // Pedidos
    Route::get('pedidos',                         [PedidoController::class, 'index']);
    Route::post('pedidos',                        [PedidoController::class, 'store']);
    Route::post('pedidos/{pedido}/calificar',     [PedidoController::class, 'calificar']);

    // Panel emprendedor
    Route::prefix('emprendedor')->group(function () {
        Route::get('/',                              [EmprendedorController::class, 'dashboard']);
        Route::post('registro',                      [EmprendedorController::class, 'registrar']);
        Route::post('local',                         [EmprendedorController::class, 'actualizarLocal']);
        Route::post('productos',                     [EmprendedorController::class, 'guardarProducto']);
        Route::post('pedidos/{pedido}/confirmar',    [EmprendedorController::class, 'confirmarPedido']);
        Route::post('pedidos/{pedido}/listo',        [EmprendedorController::class, 'marcarListo']);
    });
});
