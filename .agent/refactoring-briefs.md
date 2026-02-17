# 🎯 Refactoring Priority Briefs - Premium Cut Backend

## High Priority Tasks (Do First) 🔴

---

### **PRIORITY 1: Split Large Services**

**Objective**: Break down `ProductService` (343 lines) and `CartService` (359 lines) into smaller, focused services following Single Responsibility Principle.

**Current Issues**:
- `ProductService` handles CRUD, caching, filtering, variants, and prices
- `CartService` handles cart operations, currency logic, validation, and calculations
- Violates SRP - each service has multiple reasons to change

**Specific Actions**:

#### 1.1 Split ProductService
**Files to Create**:
- `app/Http/Services/Product/ProductCacheService.php`
- `app/Http/Services/Product/ProductFilterService.php`

**Implementation Steps**:

**Step 1**: Create `ProductCacheService`
```php
// Extract these methods from ProductService:
- generateProductCacheKey()
- getRelevantFilters()
- normalizeArray()
```

**Step 2**: Create `ProductFilterService`
```php
// Extract filter-related logic
- Move filter normalization logic
- Keep ProductService focused on CRUD only
```

**Step 3**: Update ProductService
```php
// Inject new services:
public function __construct(
    protected ProductRepositoryInterface $productRepo,
    protected ProductVariantRepository $productVariantRepo,
    protected GeoCurrencyService $geoCurrencyService,
    protected ProductVariantService $productVariantService,
    protected ProductImageService $productImageService,
    protected ProductCacheService $productCacheService,  // NEW
    protected ProductFilterService $productFilterService  // NEW
) {}

// Update methods to use injected services
```

**Expected Outcome**:
- ProductService: ~150 lines (CRUD only)
- ProductCacheService: ~80 lines
- ProductFilterService: ~100 lines

---

#### 1.2 Split CartService
**Files to Create**:
- `app/Http/Services/Cart/CartCalculationService.php`
- `app/Http/Services/Cart/CartValidationService.php`

**Implementation Steps**:

**Step 1**: Create `CartCalculationService`
```php
// Extract these methods:
- calculateItemPrice()
- calculateTotalPrice()
- recalculateCart() // NEW - combines calculate + coupon
```

**Step 2**: Create `CartValidationService`
```php
// Extract these methods:
- getCartItemData()
- Move validation logic from addToCart
```

**Step 3**: Update CartService
```php
// Inject new services and delegate to them
```

**Expected Outcome**:
- CartService: ~200 lines
- CartCalculationService: ~60 lines
- CartValidationService: ~80 lines

---

### **PRIORITY 2: Add Comprehensive Tests**

**Objective**: Increase test coverage from ~15% to 70%+ by adding unit and feature tests.

**Current State**:
- Only 5 feature tests exist
- No unit tests for services
- No repository tests
- Critical flows untested

**Specific Actions**:

#### 2.1 Create Service Unit Tests
**Files to Create**:
```
tests/Unit/Services/ProductServiceTest.php
tests/Unit/Services/CartServiceTest.php
tests/Unit/Services/OrderServiceTest.php
tests/Unit/Services/ProductVariantServiceTest.php
```

**Test Template**:
```php
<?php

namespace Tests\Unit\Services;

use App\Http\Services\Product\ProductService;
use App\Repositories\Product\ProductRepositoryInterface;
use Mockery;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    protected $productService;
    protected $productRepoMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->productRepoMock = Mockery::mock(ProductRepositoryInterface::class);
        $this->app->instance(ProductRepositoryInterface::class, $this->productRepoMock);
        
        $this->productService = app(ProductService::class);
    }

    /** @test */
    public function it_can_create_product_without_variants()
    {
        // Arrange
        $data = [
            'name_en' => 'Test Product',
            'has_variants' => false,
            // ... other fields
        ];
        
        $this->productRepoMock
            ->shouldReceive('create')
            ->once()
            ->andReturn(new \App\Models\Product($data));
        
        // Act
        $result = $this->productService->createProduct($data);
        
        // Assert
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $result);
    }
}
```

**Required Tests** (minimum 50 tests total):
- ProductService: 15 tests
- CartService: 12 tests
- OrderService: 10 tests
- ProductVariantService: 8 tests
- Other services: 5 tests

---

#### 2.2 Create Feature Tests
**Files to Create**:
```
tests/Feature/CartFlowTest.php
tests/Feature/CheckoutFlowTest.php
tests/Feature/ProductVariantTest.php
tests/Feature/FilteringTest.php
```

**Example Test**:
```php
/** @test */
public function guest_can_add_product_to_cart_and_checkout_after_registration()
{
    // 1. Create product
    $product = Product::factory()->create();
    
    // 2. Add to cart as guest
    $sessionId = 'test-session-123';
    $response = $this->postJson('/api/cart/add', [
        'product_id' => $product->id,
        'quantity' => 2,
        'session_id' => $sessionId
    ]);
    $response->assertStatus(201);
    
    // 3. Register user
    $userData = [
        'email' => 'test@example.com',
        'password' => 'password123',
        'name' => 'Test User'
    ];
    $registerResponse = $this->postJson('/api/auth/register', $userData);
    $token = $registerResponse->json('data.token');
    
    // 4. Verify cart merged
    $cartResponse = $this->withHeader('Authorization', "Bearer $token")
        ->getJson('/api/cart');
    
    $cartResponse->assertStatus(200)
        ->assertJsonPath('data.cart_items.0.product_id', $product->id)
        ->assertJsonPath('data.cart_items.0.quantity', 2);
}
```

---

### **PRIORITY 3: Fix DRY Violations**

**Objective**: Eliminate code duplication by extracting repeated logic into reusable methods.

**Specific Actions**:

#### 3.1 Fix CartService Constructor
**File**: `app/Http/Services/Cart/CartService.php`

**Current Code** (lines 27-54):
```php
protected $cartRepo, $cartItemRepo, $productVarRepo, ...;

public function __construct(
    CartRepositoryInterface  $cartRepo,
    CartItemRepository       $cartItemRepo,
    ...
) {
    $this->cartRepo = $cartRepo;
    $this->cartItemRepo = $cartItemRepo;
    // ... 10 more assignments
}
```

**Refactored Code**:
```php
public function __construct(
    protected CartRepositoryInterface $cartRepo,
    protected CartItemRepository $cartItemRepo,
    protected ProductRepositoryInterface $productRepo,
    protected ProductVariantRepository $productVarRepo,
    protected ProductValidatorService $productValidator,
    protected CartItemService $cartItemService,
    protected CouponService $couponService,
    protected GeoCurrencyService $geoCurrencyService,
    protected ProductPriceService $productPriceService,
    protected ProductSetItemsRepository $productSetRepo
) {}
```

**Action**: Replace constructor in `CartService.php` lines 27-54

---

#### 3.2 Extract Price Calculation Method
**File**: `app/Http/Services/Product/ProductVariantService.php`

**Current**: Duplicated in lines 27-38 and 93-104

**Action**: Add this method to `ProductVariantService`:
```php
/**
 * Extract default price from prices array
 *
 * @param array $prices
 * @return array ['price' => float, 'price_after_discount' => float|null]
 */
private function extractDefaultPrice(array $prices): array
{
    if (empty($prices)) {
        return ['price' => 0, 'price_after_discount' => null];
    }
    
    $priceData = collect($prices)->firstWhere('currency_id', 1) 
                ?? collect($prices)->first();
    
    return [
        'price' => $priceData['price'] ?? 0,
        'price_after_discount' => $priceData['price_after_discount'] ?? null,
    ];
}
```

**Then replace** lines 27-38:
```php
// OLD CODE - DELETE
$defaultPrice = 0;
$defaultPriceAfterDiscount = null;
if (!empty($variant['prices'])) {
    $priceData = collect($variant['prices'])->firstWhere('currency_id', 1) 
                ?? collect($variant['prices'])->first();
    if ($priceData) {
        $defaultPrice = $priceData['price'];
        $defaultPriceAfterDiscount = $priceData['price_after_discount'] ?? null;
    }
}

// NEW CODE
$prices = $this->extractDefaultPrice($variant['prices'] ?? []);
$defaultPrice = $prices['price'];
$defaultPriceAfterDiscount = $prices['price_after_discount'];
```

**Repeat** for lines 93-104 in the same file.

---

#### 3.3 Extract Cart Recalculation Method
**File**: `app/Http/Services/Cart/CartService.php`

**Current**: Repeated in `addToCart()`, `updateCartItemQuantity()`, `deleteCartItem()`

**Action**: Add this method:
```php
/**
 * Recalculate cart totals and apply coupon
 *
 * @param Cart $cart
 * @return void
 */
private function recalculateCart(Cart $cart): void
{
    $this->calculateTotalPrice($cart);
    $this->couponService->checkAndApplyCouponIfExists($cart);
}
```

**Then replace** in `addToCart()` (lines 182-183):
```php
// OLD
$this->calculateTotalPrice($cart);
$this->couponService->checkAndApplyCouponIfExists($cart);

// NEW
$this->recalculateCart($cart);
```

**Repeat** in `updateCartItemQuantity()` (lines 211-214) and `deleteCartItem()` (lines 251-252)

---

### **PRIORITY 4: Add PHPDoc Documentation**

**Objective**: Document all public methods with comprehensive PHPDoc blocks.

**Documentation Standard**:
```php
/**
 * Brief one-line description
 *
 * Detailed description if needed (optional)
 *
 * @param Type $paramName Description
 * @param Type $paramName Description
 * @return ReturnType Description
 * @throws ExceptionType When this happens
 */
```

**Specific Actions**:

#### 4.1 Document ProductService Methods
**File**: `app/Http/Services/Product/ProductService.php`

**Example for `getAllProducts` method**:
```php
/**
 * Get all products with optional filters and pagination
 *
 * Supports filtering by category, price range, attributes, and more.
 * Results are cached based on filter parameters.
 *
 * @param \Illuminate\Http\Request $request Request with filter parameters
 * @return \Illuminate\Http\JsonResponse JSON response with products or paginated products
 */
public function getAllProducts($request)
{
    // ... existing code
}
```

**Document these methods** (add PHPDoc above each):
- `getAllProducts()`
- `getBestSellers()`
- `getNewArrivals()`
- `findProduct()`
- `createProduct()`
- `updateProduct()`
- `deleteProduct()`
- `getVariantByOptions()`

---

#### 4.2 Document CartService Methods
**File**: `app/Http/Services/Cart/CartService.php`

**Document these methods**:
- `getUserCart()`
- `getGuestCart()`
- `addToCart()`
- `updateCartItemQuantity()`
- `deleteCartItem()`
- `calculateItemPrice()`
- `calculateTotalPrice()`

**Example**:
```php
/**
 * Add items to cart (supports single or bulk additions)
 *
 * Handles both authenticated users and guest sessions.
 * Validates product availability and stock before adding.
 * Automatically calculates totals and applies coupons.
 *
 * @param array $data Cart item data with product_id/variant_id and quantity
 * @return \Illuminate\Http\JsonResponse Success response with updated cart
 * @throws \Exception If product not found or out of stock
 */
public function addToCart(array $data)
{
    // ... existing code
}
```

---

#### 4.3 Document Repository Methods
**File**: `app/Repositories/Product/ProductRepository.php`

**Document these methods**:
- `getAll()`
- `find()`
- `findWithVariants()`
- `create()`
- `update()`
- `delete()`

---

### **PRIORITY 5: Fix N+1 Queries**

**Objective**: Eliminate N+1 query problems by adding proper eager loading.

**Specific Actions**:

#### 5.1 Fix ProductRepository Delete Method
**File**: `app/Repositories/Product/ProductRepository.php`

**Current Code** (line 131):
```php
$product = $this->model
    ->with(['images', 'productOptions.values.images', 'productVariants', 'productPrices'])
    ->findOrFail($id);
```

**Issue**: The `with()` clause is incomplete - missing nested relationships

**Fixed Code**:
```php
$product = $this->model
    ->with([
        'images',
        'productOptions.values.images',
        'productOptions.optionType',  // ADD THIS
        'productVariants.optionValues',  // ADD THIS
        'productVariants.productPrices',  // ADD THIS
        'productPrices'
    ])
    ->findOrFail($id);
```

**Action**: Replace line 131-135 in `ProductRepository.php`

---

#### 5.2 Add Eager Loading to Cart Retrieval
**File**: `app/Repositories/Cart/CartRepository.php`

**Check if this file exists**. If it does, ensure `findUserCart()` method includes:
```php
public function findUserCart($userId)
{
    return $this->model
        ->with([
            'cartItems.product.images',
            'cartItems.product.productPrices.currency',
            'cartItems.productVariant.optionValues.productOption',
            'cartItems.productVariant.productPrices.currency',
            'cartItems.productSetItem.products.images',
            'cartItems.currency',
            'currency'
        ])
        ->where('user_id', $userId)
        ->first();
}
```

---

## Medium Priority Tasks 🟡

### **PRIORITY 6: Improve Error Handling**

**Objective**: Standardize exception handling across the application.

**Specific Actions**:

#### 6.1 Create Custom Exception Classes
**Files to Create**:
```
app/Exceptions/ProductNotFoundException.php
app/Exceptions/InvalidCartItemException.php
app/Exceptions/CartNotFoundException.php
```

**Template**:
```php
<?php

namespace App\Exceptions;

use Exception;

class ProductNotFoundException extends Exception
{
    protected $message = 'Product not found';
    protected $code = 404;
    
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? $this->message, $this->code);
    }
}
```

---

#### 6.2 Update Handler
**File**: `app/Exceptions/Handler.php`

**Add to `register()` method**:
```php
$this->renderable(function (ProductNotFoundException $e, $request) {
    return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => []
    ], 404);
});

$this->renderable(function (InvalidCartItemException $e, $request) {
    return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
        'data' => []
    ], 400);
});
```

---

### **PRIORITY 7: Add API Documentation**

**Objective**: Generate OpenAPI/Swagger documentation for all API endpoints.

**Specific Actions**:

#### 7.1 Install Swagger Package
```bash
composer require darkaonline/l5-swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

#### 7.2 Add Swagger Annotations
**File**: `app/Http/Controllers/Product/ProductController.php`

**Example**:
```php
/**
 * @OA\Get(
 *     path="/api/products",
 *     summary="Get all products",
 *     tags={"Products"},
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="Items per page",
 *         required=false,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="data", type="array", @OA\Items())
 *         )
 *     )
 * )
 */
public function index(ProductRequest $request)
{
    return $this->service->getAllProducts($request);
}
```

---

### **PRIORITY 8: Extract Magic Numbers**

**Objective**: Replace hardcoded values with named constants.

**Specific Actions**:

#### 8.1 Create Constants Class
**File**: `app/Constants/Currency.php`

```php
<?php

namespace App\Constants;

class Currency
{
    public const DEFAULT_CURRENCY_ID = 1;
    public const EGP = 'EGP';
    public const USD = 'USD';
}
```

#### 8.2 Update ProductVariantService
**File**: `app/Http/Services/Product/ProductVariantService.php`

**Replace** line 31:
```php
// OLD
$priceData = collect($variant['prices'])->firstWhere('currency_id', 1)

// NEW
use App\Constants\Currency;

$priceData = collect($variant['prices'])->firstWhere('currency_id', Currency::DEFAULT_CURRENCY_ID)
```

---

### **PRIORITY 9: Standardize Naming**

**Objective**: Fix inconsistent variable naming throughout the codebase.

**Naming Rules**:
- Use full names, not abbreviations
- Be consistent across files
- Use descriptive names

**Specific Actions**:

#### 9.1 Fix CartService Property Names
**File**: `app/Http/Services/Cart/CartService.php`

**Rename**:
```php
// OLD
protected $productVarRepo
protected $productRepo

// NEW
protected $productVariantRepository
protected $productRepository
```

**Update all usages** in the file (use Find & Replace)

---

### **PRIORITY 10: Add Type Hints**

**Objective**: Ensure all methods have proper return type declarations.

**Specific Actions**:

#### 10.1 Add Return Types to ProductService
**File**: `app/Http/Services/Product/ProductService.php`

**Example**:
```php
// OLD
public function getAllProducts($request)

// NEW
public function getAllProducts($request): \Illuminate\Http\JsonResponse
```

**Add return types to ALL public methods** in:
- ProductService
- CartService
- OrderService
- All repositories

---

## Implementation Checklist

Use this checklist to track progress:

### High Priority
- [ ] 1.1 Split ProductService into 3 services
- [ ] 1.2 Split CartService into 3 services
- [ ] 2.1 Create 50+ unit tests
- [ ] 2.2 Create 20+ feature tests
- [ ] 3.1 Fix CartService constructor
- [ ] 3.2 Extract price calculation method
- [ ] 3.3 Extract cart recalculation method
- [ ] 4.1 Document ProductService (8 methods)
- [ ] 4.2 Document CartService (7 methods)
- [ ] 4.3 Document ProductRepository (6 methods)
- [ ] 5.1 Fix ProductRepository N+1
- [ ] 5.2 Fix CartRepository N+1

### Medium Priority
- [ ] 6.1 Create 3 custom exception classes
- [ ] 6.2 Update exception handler
- [ ] 7.1 Install Swagger
- [ ] 7.2 Add Swagger annotations (10+ endpoints)
- [ ] 8.1 Create constants class
- [ ] 8.2 Replace magic numbers (5+ locations)
- [ ] 9.1 Standardize naming in CartService
- [ ] 10.1 Add return types (50+ methods)

---

## Success Criteria

After completing all priorities:

✅ **Services**: No service exceeds 200 lines
✅ **Tests**: Coverage above 70%
✅ **Documentation**: All public methods documented
✅ **DRY**: No code block repeated more than once
✅ **Performance**: No N+1 queries in critical paths
✅ **Standards**: All methods have type hints
✅ **API Docs**: Swagger UI accessible at `/api/documentation`

---

## Notes for AI Agents

**When implementing these briefs**:
1. Always create a backup before modifying files
2. Run tests after each change
3. Update related files (imports, usages)
4. Follow PSR-12 coding standards
5. Use Laravel best practices
6. Maintain backward compatibility
7. Update composer dependencies if needed
8. Clear cache after structural changes: `php artisan cache:clear`
