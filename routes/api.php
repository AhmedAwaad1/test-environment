<?php

use App\Http\Controllers\Address\AddressController;
use App\Http\Controllers\Admin\Order\OrderController as OrderAdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Banner\BannerController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\City\CityController;
use App\Http\Controllers\Color\ColorController;
use App\Http\Controllers\Country\CountryController;
use App\Http\Controllers\District\DistrictController;
use App\Http\Controllers\Favorite\FavoriteController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\ProductType\ProductTypeController;
use App\Http\Controllers\ProductVariant\ProductVariantController;
use App\Http\Controllers\PromoCode\PromoCodeController;
use App\Http\Controllers\Size\SizeController;
use App\Http\Controllers\UserProfile\UserProfileController;
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

Route::prefix('product')->namespace('Product')->group(function () {
    // Get all size
    Route::get('/', [ProductController::class, 'index'])->name('product.index');
    // Get specific size
    Route::get('/{id}', [ProductController::class, 'show'])->name('product.show');
    // Create size
    Route::post('/', [ProductController::class, 'store'])->name('product.store');
    // Update size
    Route::put('/{id}', [ProductController::class, 'update'])->name('product.update');
    // Delete size
    Route::delete('/{id}', [ProductController::class, 'destroy'])->name('product.destroy');
});

Route::prefix('banner')->namespace('Banner')->group(function () {
    // Get all banners
    Route::get('/', [BannerController::class, 'index'])->name('banner.index');
    // Get specific banner
    Route::get('/{id}', [BannerController::class, 'show'])->name('banner.show');
    // Create banner
    Route::post('/', [BannerController::class, 'store'])->name('banner.store');
    // Update banner
    Route::put('/{id}', [BannerController::class, 'update'])->name('banner.update');
    // Delete banner
    Route::delete('/{id}', [BannerController::class, 'destroy'])->name('banner.destroy');
});

Route::prefix('product-variant')->namespace('ProductVariant')->group(function () {
    // Get all product variants
    Route::get('/', [ProductVariantController::class, 'index'])->name('product-variant.index');
    // Get specific product variant
    Route::get('/{id}', [ProductVariantController::class, 'show'])->name('product-variant.show');
    // Create product variant
    Route::post('/', [ProductVariantController::class, 'store'])->name('product-variant.store');
    // Update product variant
    Route::put('/{id}', [ProductVariantController::class, 'update'])->name('product-variant.update');
    // Delete product variant
    Route::delete('/{id}', [ProductVariantController::class, 'destroy'])->name('product-variant.destroy');
});

Route::prefix('district')->namespace('District')->group(function () {
    // Get all districts
    Route::get('/', [DistrictController::class, 'index'])->name('district.index');
    // Get specific district
    Route::get('/{id}', [DistrictController::class, 'show'])->name('district.show');
    // Create district
    Route::post('/', [DistrictController::class, 'store'])->name('district.store');
    // Update district
    Route::put('/{id}', [DistrictController::class, 'update'])->name('district.update');
    // Delete district
    Route::delete('/{id}', [DistrictController::class, 'destroy'])->name('district.destroy');
});

Route::prefix('address')->namespace('Addres')->group(function () {
        // Get all user addresses
        Route::get('/', [AddressController::class, 'index'])->name('address.index');
        // Get specific address
        Route::get('/{id}', [AddressController::class, 'show'])->name('address.show');
        // Create new address
        Route::post('/', [AddressController::class, 'store'])->name('address.store');
        // Update address
        Route::put('/{id}', [AddressController::class, 'update'])->name('address.update');
        // Delete address
        Route::delete('/{id}', [AddressController::class, 'destroy'])->name('address.destroy');
});

Route::prefix('promo-code')->namespace('PromoCode')->group(function () {
    // Get all promo codes
    Route::get('/', [PromoCodeController::class, 'index'])->name('promo-code.index');
    // Get specific promo code
    Route::get('/{id}', [PromoCodeController::class, 'show'])->name('promo-code.show');
    // Create promo code
    Route::post('/', [PromoCodeController::class, 'store'])->name('promo-code.store');
    // Update promo code
    Route::put('/{id}', [PromoCodeController::class, 'update'])->name('promo-code.update');
    // Delete promo code
    Route::delete('/{id}', [PromoCodeController::class, 'destroy'])->name('promo-code.destroy');
});

Route::prefix('cart')->namespace('Cart')->group(function () {
    // Get cart by user id
    Route::get('/', [CartController::class, 'getUserCart'])->name('cart.show');
    // Add to cart
    Route::post('/add', [CartController::class, 'addtoCart'])->name('cart.add');
    // Update cart item
    Route::put('/item/{id}', [CartController::class, 'updateCartItemQuantity'])->name('cart.update');
    // Delete cart item
    Route::delete('/item/{id}', [CartController::class, 'deleteCartItem'])->name('cart.delete');

    //apply coupon
    Route::post('/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.apply-coupon');
    //remove coupon
    Route::post('/remove-coupon', [CartController::class, 'removeCoupon'])->name('cart.remove-coupon');
});

Route::prefix('order')->namespace('Order')->group(function () {
    // Get all user orders
    Route::get('/', [OrderController::class, 'index'])->name('order.index');
    // Get specific order
    Route::get('/{id}', [OrderController::class, 'show'])->name('order.show');
    // Create order
    Route::post('/checkout', [OrderController::class, 'checkout'])->name('order.checkout');
    // Update order
    Route::put('/{id}', [OrderController::class, 'update'])->name('order.update');
    // Delete order
    Route::delete('/{id}', [OrderController::class, 'destroy'])->name('order.destroy');
});

Route::prefix('admin/order')->middleware('role.admin')->group(function () {
    Route::get('/', [OrderAdminController::class, 'index'])->name('admin.orders.index');
    Route::get('/{id}', [OrderAdminController::class, 'show'])->name('admin.orders.show');
    Route::put('/{id}', [OrderAdminController::class, 'update'])->name('admin.orders.update');
});

Route::prefix('favorite')->namespace('Favorite')->group(function () {
    // Get all user favorites
    Route::get('/', [FavoriteController::class, 'index'])->name('favorite.index');
    // Get specific favorite
    Route::get('/{id}', [FavoriteController::class, 'show'])->name('favorite.show');
    // Create favorite
    Route::post('/', [FavoriteController::class, 'store'])->name('favorite.store');
    // Delete favorite
    Route::delete('/{id}', [FavoriteController::class, 'destroy'])->name('favorite.destroy');
});

Route::prefix('user-profile')->namespace('UserProfile')->group(function () {
    // Update user profile
    Route::put('/', [UserProfileController::class, 'update'])->name('user-profile.update');
});
