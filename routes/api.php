<?php

use App\Http\Controllers\Address\AddressController;
use App\Http\Controllers\Admin\Order\OrderController as OrderAdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Banner\BannerController;
use App\Http\Controllers\Blog\BlogController;
use App\Http\Controllers\Cart\CartController;
use App\Http\Controllers\Cart\CouponController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\City\CityController;
use App\Http\Controllers\Contact\ContactController;
use App\Http\Controllers\Country\CountryController;
use App\Http\Controllers\Currency\CurrencyController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\District\DistrictController;
use App\Http\Controllers\Favorite\FavoriteController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Payment\TapMockController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Product\ProductVariantController;
use App\Http\Controllers\ProductPrice\ProductPriceController;
use App\Http\Controllers\ProductSetItems\ProductSetItemsController;
use App\Http\Controllers\PromoCode\PromoCodeController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\SubCategory\SubCategoryController;
use App\Http\Controllers\Subscribe\SubscribeController;
use App\Http\Controllers\Testimonial\TestimonialController;

// Add this line
use App\Http\Controllers\Product\ProductOptionTypeController;
use App\Http\Controllers\UserProfile\UserProfileController;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\TapRedirectController;
use App\Http\Controllers\Payment\TapWebhookController;
use App\Http\Controllers\Product\ProductOptionController;
use App\Http\Controllers\Product\ProductAttributeFilterController;
use App\Http\Controllers\Review\ReviewController;
use App\Http\Controllers\ERP\ErpCategoryController;
use App\Http\Controllers\ERP\ErpProductController;

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

    // 🔐 Forgot Password Flow
    Route::post('send-reset-code', [AuthController::class, 'sendResetCodeToEmail'])->name('auth.send-reset-code')->withoutMiddleware('auth:api');
    Route::post('verify-reset-code', [AuthController::class, 'verifyResetCode'])->name('auth.verify-reset-code')->withoutMiddleware('auth:api');
    Route::post('reset-password', [AuthController::class, 'resetPasswordWithCode'])->name('auth.reset-password')->withoutMiddleware('auth:api');

    // Verify Email
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('auth.verify_email')->withoutMiddleware('auth:api');
    // Resend Verification Email
    Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail'])
         ->name('auth.resend_verification_email')->withoutMiddleware('auth:api');
});

Route::prefix('country')->namespace('Country')->group(function () {
    // Get all countries
    Route::get('/', [CountryController::class, 'index'])->name('country.index');
    // Get country by IP
    Route::get('/geo', [CountryController::class, 'getCountriesByIp'])->name('country.geo');
    // Get specific country
    Route::get('/{id}', [CountryController::class, 'show'])->name('country.show');

    Route::middleware('auth:api')->group(function () {
        // Create country
        Route::post('/', [CountryController::class, 'store'])->name('country.store');
        // Update country
        Route::put('/{id}', [CountryController::class, 'update'])->name('country.update');
        // Delete country
        Route::delete('/{id}', [CountryController::class, 'destroy'])->name('country.destroy');
    });
});

Route::prefix('city')->namespace('City')->group(function () {
    // Get all cities by IP
    Route::get('/geo-cities', [CityController::class, 'getAllCitiesByIp'])->name('city.geo-cities');
    // Get all cities
    Route::get('/', [CityController::class, 'index'])->name('city.index');
    // Get specific city
    Route::get('/{id}', [CityController::class, 'show'])->name('city.show');

    Route::middleware('auth:api')->group(function () {
        // Create city
        Route::post('/', [CityController::class, 'store'])->name('city.store');
        // Update city
        Route::put('/{id}', [CityController::class, 'update'])->name('city.update');
        // Delete city
        Route::delete('/{id}', [CityController::class, 'destroy'])->name('city.destroy');
    });
});

Route::prefix('sub-category')->namespace('SubCategory')->group(function () {
    // Get all sub-categories
    Route::get('/', [SubCategoryController::class, 'index'])->name('sub-category.index');
    // Get specific sub-category
    Route::get('/{id}', [SubCategoryController::class, 'show'])->name('sub-category.show');

    Route::middleware('auth:api')->group(function () {
        // Create sub-category
        Route::post('/', [SubCategoryController::class, 'store'])->name('sub-category.store');
        // Update sub-category
        Route::put('/{id}', [SubCategoryController::class, 'update'])->name('sub-category.update');
        // Delete sub-category
        Route::delete('/{id}', [SubCategoryController::class, 'destroy'])->name('sub-category.destroy');
    });
});

Route::prefix('category')->namespace('Category')->group(function () {
    // Get all categories
    Route::get('/', [CategoryController::class, 'index'])->name('category.index');
    // Get specific category
    Route::get('/{id}', [CategoryController::class, 'show'])->name('category.show');

    Route::middleware('auth:api')->group(function () {
        // Create category
        Route::post('/', [CategoryController::class, 'store'])->name('category.store');
        // Update category
        Route::put('/{id}', [CategoryController::class, 'update'])->name('category.update');
        // Delete category
        Route::delete('/{id}', [CategoryController::class, 'destroy'])->name('category.destroy');
    });
});

// Product Management
Route::prefix('products')->group(function () {
    // Basic Product CRUD
    Route::get('/', [ProductController::class, 'index'])->name('products.index');

    // Specific endpoints first
    Route::get('/new-arrivals', [ProductController::class, 'newArrivals'])->name('products.new-arrivals');
    Route::get('/best-sellers', [ProductController::class, 'bestSellers'])->name('products.best-sellers');

    // Catch-all by ID last
    Route::get('/{id}', [ProductController::class, 'show'])->name('products.show');

    Route::middleware(['auth:api', 'role.admin'])->group(function () {
        Route::post('/', [ProductController::class, 'store'])->name('products.store');
        Route::put('/{id}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    // --- Product Variants ---
    Route::prefix('{productId}/variants')->group(function () {
        Route::get('/', [ProductVariantController::class, 'index'])->name('products.variants.index');
        Route::post('/get-by-options', [ProductVariantController::class, 'getByOptions'])->name('products.variants.get-by-options');
    });

    // --- Product Options & Values ---
    Route::prefix('{productId}/options')->group(function () {
        Route::get('/', [ProductOptionController::class, 'index'])->name('products.options.index');
        Route::middleware('auth:api')->group(function () {
            Route::post('/', [ProductOptionController::class, 'store'])->name('products.options.store');
        });
    });
});

// Standalone Variant/Option Management (for Update/Delete)
Route::prefix('product-variants')->group(function () {
    Route::middleware('auth:api')->group(function () {
        Route::post('/', [ProductVariantController::class, 'store'])->name('product-variants.store');
        Route::put('/{id}', [ProductVariantController::class, 'update'])->name('product-variants.update');
        Route::patch('/{id}/stock', [ProductVariantController::class, 'updateStock'])->name('product-variants.update-stock');
        Route::patch('/{id}/toggle-status', [ProductVariantController::class, 'toggleStatus'])->name('product-variants.toggle-status');
        Route::delete('/{id}', [ProductVariantController::class, 'destroy'])->name('product-variants.destroy');
    });
});

Route::prefix('product-options')->group(function () {
    Route::get('/types', [ProductOptionTypeController::class, 'index'])->name('product-options.types');

    Route::middleware('auth:api')->group(function () {
        Route::put('/{id}', [ProductOptionController::class, 'update'])->name('product-options.update');
        Route::delete('/{id}', [ProductOptionController::class, 'destroy'])->name('product-options.destroy');

        // Values within Options
        Route::post('/{id}/values', [ProductOptionController::class, 'addValue'])->name('product-options.add-value');
        Route::put('/values/{valueId}', [ProductOptionController::class, 'updateValue'])->name('product-options.update-value');
        Route::delete('/values/{valueId}', [ProductOptionController::class, 'deleteValue'])->name('product-options.delete-value');
        Route::post('/{id}/reorder-values', [ProductOptionController::class, 'reorder-values'])->name('product-options.reorder-values');
    });
});

    Route::prefix('option-types')->group(function () {
    Route::get('/', [ProductOptionTypeController::class, 'index'])->name('option-types.index');
    Route::get('/{id}', [ProductOptionTypeController::class, 'show'])->name('option-types.show');

    Route::middleware('auth:api')->group(function () {
        Route::post('/', [ProductOptionTypeController::class, 'store'])->name('option-types.store');
        Route::put('/{id}', [ProductOptionTypeController::class, 'update'])->name('option-types.update');
        Route::delete('/{id}', [ProductOptionTypeController::class, 'destroy'])->name('option-types.destroy');
    });
});

// Keep the old one for backward compatibility if needed by frontend
Route::get('option-type', [ProductOptionTypeController::class, 'index']);

// Dynamic Product Attribute & Filter Discovery
Route::get('product-attributes/filters', [ProductAttributeFilterController::class, 'index'])
    ->name('product-attributes.filters');
Route::post('product-attributes/filter-products', [ProductAttributeFilterController::class, 'filter'])
    ->name('product-attributes.filter-products');

Route::prefix('banner')->namespace('Banner')->group(function () {
    // Get all banners
    Route::get('/', [BannerController::class, 'index'])->name('banner.index');
    // Get specific banner
    Route::get('/{id}', [BannerController::class, 'show'])->name('banner.show');

    Route::middleware('auth:api')->group(function () {
        // Create banner
        Route::post('/', [BannerController::class, 'store'])->name('banner.store');
        // Update banner
        Route::put('/{id}', [BannerController::class, 'update'])->name('banner.update');
        // Delete banner
        Route::delete('/{id}', [BannerController::class, 'destroy'])->name('banner.destroy');
    });
});

Route::prefix('district')->namespace('District')->group(function () {
    // Get all districts
    Route::get('/', [DistrictController::class, 'index'])->name('district.index');
    // Get specific district
    Route::get('/{id}', [DistrictController::class, 'show'])->name('district.show');

    Route::middleware('auth:api')->group(function () {
        // Create district
        Route::post('/', [DistrictController::class, 'store'])->name('district.store');
        // Update district
        Route::put('/{id}', [DistrictController::class, 'update'])->name('district.update');
        // Delete district
        Route::delete('/{id}', [DistrictController::class, 'destroy'])->name('district.destroy');
    });
});

Route::prefix('address')->namespace('Address')->middleware('auth:api')->group(function () {
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

    Route::middleware('auth:api')->group(function () {
        // Create promo code
        Route::post('/', [PromoCodeController::class, 'store'])->name('promo-code.store');
        // Update promo code
        Route::put('/{id}', [PromoCodeController::class, 'update'])->name('promo-code.update');
        // Delete promo code
        Route::delete('/{id}', [PromoCodeController::class, 'destroy'])->name('promo-code.destroy');
    });
});

Route::prefix('cart')->namespace('Cart')->group(function () {
    // Get cart by user id
    Route::get('/', [CartController::class, 'getUserCart'])->name('cart.show');

    // Get cart by session id
    Route::get('/guest', [CartController::class, 'getGuestCart'])->name('cart.guest.show');

    // Add to cart
    Route::post('/add', [CartController::class, 'addtoCart'])->name('cart.add');
    // Update cart item
    Route::put('/item/{id}', [CartController::class, 'updateCartItemQuantity'])->name('cart.update');
    // Delete cart item
    Route::delete('/item/{id}', [CartController::class, 'deleteCartItem'])->name('cart.delete');

    //apply coupon
    Route::post('/apply-coupon', [CouponController::class, 'applyCoupon'])->name('cart.apply-coupon');
    //remove coupon
    Route::post('/remove-coupon', [CouponController::class, 'removeCoupon'])->name('cart.remove-coupon');
});

Route::prefix('order')->namespace('Order')->group(function () {
    // Create order (Allows Guest Checkout)
    Route::post('/checkout', [OrderController::class, 'checkout'])->name('order.checkout');

    Route::middleware('auth:api')->group(function () {
        // Get all user orders
        Route::get('/', [OrderController::class, 'index'])->name('order.index');
        // Get specific order
        Route::get('/{id}', [OrderController::class, 'show'])->name('order.show');
    });
});

Route::prefix('admin/order')->middleware(['auth:api', 'role.admin'])->group(function () {
    Route::get('/', [OrderAdminController::class, 'index'])->name('admin.orders.index');
    Route::get('/{id}', [OrderAdminController::class, 'show'])->name('admin.orders.show');
    Route::put('/{id}', [OrderAdminController::class, 'update'])->name('admin.orders.update');
});

Route::prefix('favorite')->namespace('Favorite')->middleware('auth:api')->group(function () {
    // Delete all favorites
    Route::delete('/all', [FavoriteController::class, 'deleteAllFavorite'])->name('favorite.delete_all');
    // Get all user favorites
    Route::get('/', [FavoriteController::class, 'index'])->name('favorite.index');
    // Get user favorites by auth
    Route::get('/user-auth', [FavoriteController::class, 'userFavorites'])->name('favorite.user_auth');
    // Get specific favorite
    Route::get('/{id}', [FavoriteController::class, 'show'])->name('favorite.show');
    // Create favorite
    Route::post('/', [FavoriteController::class, 'store'])->name('favorite.store');
    // Delete favorite
    Route::delete('/{id}', [FavoriteController::class, 'destroy'])->name('favorite.destroy');
});

Route::prefix('user-profile')->namespace('UserProfile')->middleware('auth:api')->group(function () {
    // Update user profile
    Route::put('/', [UserProfileController::class, 'update'])->name('user-profile.update');
});


Route::prefix('contact')->namespace('Contact')->group(function () {
    Route::get('/{id}', [ContactController::class, 'show'])->name('contact.show');
    Route::post('/', [ContactController::class, 'store'])->name('contact.store');

    Route::middleware('auth:api')->group(function () {
        Route::get('/', [ContactController::class, 'index'])->name('contact.index');
        Route::delete('/{id}', [ContactController::class, 'destroy'])->name('contact.destroy');
        // Mark as checked form
        Route::put('/{id}/mark-checked', [ContactController::class, 'markAsChecked'])
             ->name('contact.mark_checked');
    });
});

Route::prefix('blog')->namespace('Blog')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('blog.show');

    Route::middleware('auth:api')->group(function () {
        Route::post('/', [BlogController::class, 'store'])->name('blog.store');
        Route::put('/{id}', [BlogController::class, 'update'])->name('blog.update');
        Route::delete('/{id}', [BlogController::class, 'destroy'])->name('blog.destroy');
    });
});

// Review Routes
Route::prefix('review')->namespace('Review')->group(function () {
    // Get all Reviews
    Route::get('/', [ReviewController::class, 'all'])->name('review.all');
    // Get a specific Review
    Route::get('/{id}', [ReviewController::class, 'show'])->name('review.get');

    Route::middleware('auth:api')->group(function () {
        // Create a new Review
        Route::post('/', [ReviewController::class, 'create'])->name('review.create');
        // Delete a Review
        Route::delete('/{id}', [ReviewController::class, 'delete'])->name('review.delete');
    });
});

Route::prefix('dashboard')->namespace('Dashboard')->middleware(['auth:api', 'role.admin'])->group(function () {
    Route::get('/stats', [DashboardController::class, 'getStatistics'])->name('admin.dashboard.stats');
    Route::get('/order-report', [DashboardController::class, 'getOrderStatistics'])->name('admin.orders.order_report');
});

Route::prefix('product-set-items')->group(function () {
    // Get all Product Set Items Management
    Route::get('/', [ProductSetItemsController::class, 'index'])->name('product-set-items.index');
    // Get Product Set Item
    Route::get('/{id}', [ProductSetItemsController::class, 'show'])->name('product-set-items.show');

    Route::middleware('auth:api')->group(function () {
        // Create a new Product Set Item
        Route::post('/', [ProductSetItemsController::class, 'store'])->name('product-set-items.store');
        // Update a specific Product Set Item
        Route::put('/{id}', [ProductSetItemsController::class, 'update'])->name('product-set-items.update');
        // Delete a specific Product Set Item
        Route::delete('/{id}', [ProductSetItemsController::class, 'destroy'])->name('product-set-items.destroy');
    });
});

Route::prefix('product-price')->group(function () {
    // Get all Product Prices
    Route::get('/', [ProductPriceController::class, 'index'])->name('product-price.index');
    // Get a specific Product Price
    Route::get('/{id}', [ProductPriceController::class, 'show'])->name('product-price.show');

    Route::middleware('auth:api')->group(function () {
        // Create a new Product Price
        Route::post('/', [ProductPriceController::class, 'store'])->name('product-price.store');
        // Update a specific Product Price
        Route::put('/{id}', [ProductPriceController::class, 'update'])->name('product-price.update');
        // Delete a specific Product Price
        Route::delete('/{id}', [ProductPriceController::class, 'destroy'])->name('product-price.destroy');
    });
});

Route::get('/currencies', [CurrencyController::class, 'index'])->name('currencies.index');


// Product Options
Route::prefix('subscribe')->namespace('Subscribe')->group(function () {
    Route::post('/', [SubscribeController::class, 'store'])->name('subscribe.store');

    Route::middleware(['auth:api', 'role.admin'])->group(function () {
        Route::get('/', [SubscribeController::class, 'index'])->name('subscribe.index');
        Route::post('/send-email', [SubscribeController::class, 'sendEmailToSubscribers'])->name('subscribe.send-email');
    });
});


// Testimonial Routes
Route::prefix('testimonial')->namespace('Testimonial')->group(function () {
    // Get all Testimonials
    Route::get('/', [TestimonialController::class, 'all'])->name('testimonial.all');
    // Get a specific Testimonial
    Route::get('/{id}', [TestimonialController::class, 'show'])->name('testimonial.get');

    Route::middleware('auth:api')->group(function () {
        // Create a new Testimonial
        Route::post('/', [TestimonialController::class, 'create'])->name('testimonial.create');
        // Delete a Testimonial
        Route::delete('/{id}', [TestimonialController::class, 'delete'])->name('testimonial.delete');
    });
});


Route::get('/payments/tap/mock/complete', [TapMockController::class, 'complete'])->name('payments.tap.mock.complete');

// Tap Payments Webhook and Redirect Routes
Route::post('/payments/tap/webhook', [TapWebhookController::class, 'handleWebhook'])->name('payments.tap.webhook');
Route::get('/payments/tap/redirect', [TapRedirectController::class, 'handleRedirect'])->name('payments.tap.redirect');

// Quiz Routes
Route::prefix('quiz')->group(function () {
    // Get  quizz
    Route::get('/{id}', [QuizController::class, 'show'])->name('quiz.show');
    // Submit quiz answers
    Route::post('/submit', [QuizController::class, 'submit'])->name('quiz.submit');
});

// ERP Integration Routes (Categories & Products Sync)
Route::prefix('v1/erp')->group(function () {
    Route::post('categories', [ErpCategoryController::class, 'store'])->name('erp.categories.store');
    Route::get('categories', [ErpCategoryController::class, 'index'])->name('erp.categories.index');
    Route::post('products', [ErpProductController::class, 'store'])->name('erp.products.store');
    Route::post('products/bulk', [ErpProductController::class, 'bulkUpsert'])->name('erp.products.bulk-upsert');
    Route::get('products', [ErpProductController::class, 'index'])->name('erp.products.index');
    Route::get('products/{sku}', [ErpProductController::class, 'show'])->name('erp.products.show');
    Route::put('products/{sku}', [ErpProductController::class, 'update'])->name('erp.products.update');
    Route::delete('products/{sku}', [ErpProductController::class, 'destroy'])->name('erp.products.destroy');
});
