<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResearchController;

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
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::resources([
    'roles' => RoleController::class,
    'users' => UserController::class,
    'products' => ProductController::class,
]);

Route::get('products/view_pdf/{id}', [ProductController::class, 'viewPdf'])->name('products.view_pdf');
Route::post('products/{product}', [ProductController::class, 'updates'])->name('products.updates');

Route::get('research-index', [ResearchController::class, 'index'])->name('research.index');
Route::get('/items/search', [ResearchController::class, 'search'])->name('items.search');
Route::get('/products/keywords/autocomplete', [ResearchController::class, 'keywordAutocomplete'])->name('products.keywords.autocomplete');
Route::post('/products/approve/{product}', [ResearchController::class, 'approve'])->name('products.approve');

Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');


