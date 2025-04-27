<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\City\CityController;
use App\Http\Controllers\Color\ColorController;
use App\Http\Controllers\Country\CountryController;
use App\Http\Controllers\ProductType\ProductTypeController;
use App\Http\Controllers\Size\SizeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function () {
    //login
    Route::post('login', [AuthController::class, 'login'])->name('auth.login');
    // Registration
    Route::post('register', [AuthController::class, 'register'])->name('auth.register');
    // Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth:api');
    // Refresh Token
    Route::post('refresh-token', [AuthController::class, 'refresh'])->name('auth.refresh')->middleware('auth:api');
});

Route::prefix('country')->namespace('Country')->group(function () {
    // Get all countries
    Route::get('/', [CountryController::class, 'index'])->name('country.index');
    // Get specific country
    Route::get('/{id}', [CountryController::class, 'show'])->name('country.show');
    // Create country
    Route::post('/', [CountryController::class, 'store'])->name('country.store');
    // Update country
    Route::put('/{id}', [CountryController::class, 'update'])->name('country.update');
    // Delete country
    Route::delete('/{id}', [CountryController::class, 'destroy'])->name('country.destroy');
});

Route::prefix('city')->namespace('City')->group(function () {
    // Get all cities
    Route::get('/', [CityController::class, 'index'])->name('city.index');
    // Get specific city
    Route::get('/{id}', [CityController::class, 'show'])->name('city.show');
    // Create city
    Route::post('/', [CityController::class, 'store'])->name('city.store');
    // Update city
    Route::put('/{id}', [CityController::class, 'update'])->name('city.update');
    // Delete city
    Route::delete('/{id}', [CityController::class, 'destroy'])->name('city.destroy');
});

Route::prefix('product-type')->namespace('ProductType')->group(function () {
    // Get all product types
    Route::get('/', [ProductTypeController::class, 'index'])->name('product-type.index');
    // Get specific product type
    Route::get('/{id}', [ProductTypeController::class, 'show'])->name('product-type.show');
    // Create product type
    Route::post('/', [ProductTypeController::class, 'store'])->name('product-type.store');
    // Update product type
    Route::put('/{id}', [ProductTypeController::class, 'update'])->name('product-type.update');
    // Delete product type
    Route::delete('/{id}', [ProductTypeController::class, 'destroy'])->name('product-type.destroy');
});

Route::prefix('category')->namespace('Category')->group(function () {
    // Get all categories
    Route::get('/', [CategoryController::class, 'index'])->name('category.index');
    // Get specific category
    Route::get('/{id}', [CategoryController::class, 'show'])->name('category.show');
    // Create category
    Route::post('/', [CategoryController::class, 'store'])->name('category.store');
    // Update category
    Route::put('/{id}', [CategoryController::class, 'update'])->name('category.update');
    // Delete category
    Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('category.destroy');
});

Route::prefix('color')->namespace('Color')->group(function () {
    // Get all colors
    Route::get('/', [ColorController::class, 'index'])->name('color.index');
    // Get specific color
    Route::get('/{id}', [ColorController::class, 'show'])->name('color.show');
    // Create color
    Route::post('/', [ColorController::class, 'store'])->name('color.store');
    // Update color
    Route::put('/{id}', [ColorController::class, 'update'])->name('color.update');
    // Delete color
    Route::delete('/{id}', [ColorController::class, 'destroy'])->name('color.destroy');
});

Route::prefix('size')->namespace('Size')->group(function () {
    // Get all size
    Route::get('/', [SizeController::class, 'index'])->name('size.index');
    // Get specific size
    Route::get('/{id}', [SizeController::class, 'show'])->name('size.show');
    // Create size
    Route::post('/', [SizeController::class, 'store'])->name('size.store');
    // Update size
    Route::put('/{id}', [SizeController::class, 'update'])->name('size.update');
    // Delete size
    Route::delete('/{id}', [SizeController::class, 'destroy'])->name('size.destroy');
});
