<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MorososController;
use App\Http\Controllers\DemandadoController;
use App\Http\Controllers\PromesaClienteController;
use App\Http\Controllers\CartasController;
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
Route::get('/demandado', [DemandadoController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('demandado.index');
Route::get('/cartas', [CartasController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('cartas.index');

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
Route::post('/morosos/pagado-masivo', [MorososController::class, 'marcarPagadosMasivo'])->name('morosos.pagado_masivo');
Route::post('/morosos/whatsapp-test', [MorososController::class, 'enviarWhatsappTest'])
    ->middleware(['auth', 'verified'])
    ->name('morosos.whatsapp_test');

Route::get('/promesa_cliente', [PromesaClienteController::class, 'index'])
    ->name('promesa_cliente');
Route::post('/promesa_cliente', [PromesaClienteController::class, 'store'])
    ->name('promesa_cliente.store');

    Route::get('/morosos/subir-excel', function () {
        return view('morosos.subir_excel');
    });
    Route::post('/morosos/subir-excel', [MorososController::class, 'subirExcel'])
    ->middleware(['auth', 'verified'])
    ->name('morosos.subir-excel');
    Route::post('/morosos/cartas-seleccionadas', [MorososController::class, 'generarPDFSeleccionados'])
    ->name('morosos.cartas_seleccionadas');

    Route::get('/morosos/pdf', [MorososController::class, 'generarPDF']);

    Route::post('/cartas/importar-excel', [CartasController::class, 'importarExcel'])
        ->name('cartas.importar_excel');

    Route::delete('/cartas/limpiar', [CartasController::class, 'limpiar'])
        ->name('cartas.limpiar');
        Route::post('/cartas/generar-pdf', [CartasController::class, 'generarPdf'])
    ->name('cartas.generar_pdf');

    Route::get('/morosos/whatsapp/clientes', [MorososController::class, 'whatsappClientes'])
    ->name('morosos.whatsapp.clientes');

    Route::get('/morosos/whatsapp/conversacion/{documento}', [MorososController::class, 'whatsappConversacion'])
    ->name('morosos.whatsapp.conversacion');

    Route::get('/cartas/moratoria', [CartasController::class, 'moratoria'])
    ->name('cartas.moratoria');

Route::post('/cartas/moratoria/generar-pdf', [CartasController::class, 'generarPdfMoratoria'])
    ->name('cartas.moratoria.generar_pdf');
    
require __DIR__.'/auth.php';
