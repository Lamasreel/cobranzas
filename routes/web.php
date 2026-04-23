<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MorososController;
use App\Http\Controllers\PromesaClienteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/morosos', [MorososController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('morosos.index');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/morosos/whatsapp-auto', [MorososController::class, 'whatsappAutomationShow'])
        ->name('morosos.whatsapp_automation.show');
    Route::post('/morosos/whatsapp-auto', [MorososController::class, 'whatsappAutomationUpdate'])
        ->name('morosos.whatsapp_automation.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/morosos/promesa', [MorososController::class, 'actualizarPromesa'])->name('morosos.promesa');
Route::post('/morosos/pagado/{id}', [MorososController::class, 'marcarPagado'])->name('morosos.pagado');
Route::post('/morosos/whatsapp-test', [MorososController::class, 'enviarWhatsappTest'])
    ->middleware(['auth', 'verified'])
    ->name('morosos.whatsapp_test');

Route::get('/promesa_cliente', [PromesaClienteController::class, 'index'])
    ->name('promesa_cliente');
Route::post('/promesa_cliente', [PromesaClienteController::class, 'store'])
    ->name('promesa_cliente.store');

require __DIR__.'/auth.php';
