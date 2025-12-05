<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Api\AuthController;


use App\Http\Controllers\Api\PerawatController;

// Menampilkan daftar antrian
// Route::get('/perawat', function () {
//     $controller = new PerawatController();
//     $response = $controller->index();  // ambil data dari controller
//     $antrian = $response->getData(true)['data']; // ambil array

//     return view('perawat', ['antrian' => $antrian]);
// });
// Route::post('/perawat/input-vital/{id}', [PerawatController::class, 'storeVital'])->name('perawat.storeVital');
Route::get('/', function () {
    return view('welcome');
});

Route::post('/register', [AuthController::class, 'register']);

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';


