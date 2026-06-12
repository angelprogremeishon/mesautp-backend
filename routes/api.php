<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmprendedorController;
use App\Http\Controllers\Api\LocalesController;
use App\Http\Controllers\Api\PedidoController;
use Illuminate\Support\Facades\Route;

// ── Auth (público) ────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    // Flujo nuevo: registro con enlace + ingreso con PIN
    Route::post('check-email',        [AuthController::class, 'checkEmail']);
    Route::post('send-link',          [AuthController::class, 'sendLink']);          // envía enlace de registro
    Route::post('verify',             [AuthController::class, 'verifyToken']);       // valida token del enlace
    Route::post('completar-registro', [AuthController::class, 'completeRegistro']);  // crea cuenta + PIN
    Route::post('login-pin',          [AuthController::class, 'loginPin']);          // ingreso con PIN

    // Emprendedor (correo + contraseña) — se mantiene
    Route::post('emprendedor/register', [AuthController::class, 'emprendedorRegister']);
    Route::post('emprendedor/login',    [AuthController::class, 'emprendedorLogin']);
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
        Route::get('calificaciones',                 [EmprendedorController::class, 'calificaciones']);
        Route::post('registro',                      [EmprendedorController::class, 'registrar']);
        Route::post('local',                         [EmprendedorController::class, 'actualizarLocal']);
        Route::post('productos',                     [EmprendedorController::class, 'guardarProducto']);
        Route::post('pedidos/{pedido}/confirmar',    [EmprendedorController::class, 'confirmarPedido']);
        Route::post('pedidos/{pedido}/listo',        [EmprendedorController::class, 'marcarListo']);
        Route::post('pedidos/{pedido}/entregar',     [EmprendedorController::class, 'marcarEntregado']);
    });
});
