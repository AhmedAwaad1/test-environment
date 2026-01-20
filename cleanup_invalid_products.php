<?php

use App\Models\Product;
use App\Repositories\Product\ProductRepository;
use Illuminate\Support\Facades\DB;

/**
 * PRODUCTION CLEANUP SCRIPT
 * 
 * This script deletes products that are marked as having variants (has_variants = true)
 * but do not actually have any associated variant records.
 * 
 * It uses the ProductRepository::delete method to ensure all related data 
 * (images, prices, options) are cleaned up correctly.
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// --- SAFETY CONFIGURATION ---
// Set this to false ONLY when you are ready to delete for real.
$dryRun = true; 
// ----------------------------

$productRepo = app(ProductRepository::class);

$invalidProductIds = Product::where('has_variants', true)
    ->whereDoesntHave('productVariants')
    ->pluck('id');

$total = count($invalidProductIds);

if ($total === 0) {
    echo "\033[32mNo invalid products found. Your database is clean!\033[0m\n";
    exit;
}

if ($dryRun) {
    echo "\033[33m[DRY RUN MODE]\033[0m No products will be deleted.\n";
    echo "Found \033[1m$total\033[0m products that meet the criteria for deletion.\n";
    echo "Sample IDs: " . implode(', ', $invalidProductIds->take(10)->toArray()) . "...\n";
    echo "\nTo perform the actual deletion, edit this file and set \033[1m\$dryRun = false;\033[0m\n";
} else {
    echo "\033[31m[REAL MODE]\033[0m Starting deletion of \033[1m$total\033[0m products...\n";
    echo "This may take a while as it cleans up images and related records.\n\n";
    
    $deletedCount = 0;
    $errorCount = 0;
    
    foreach ($invalidProductIds as $id) {
        try {
            $productRepo->delete($id);
            $deletedCount++;
            
            // Progress reporting
            if ($deletedCount % 50 == 0 || $deletedCount == $total) {
                $percentage = round(($deletedCount / $total) * 100);
                echo "Progress: [$deletedCount/$total] ($percentage%)\n";
            }
        } catch (\Exception $e) {
            $errorCount++;
            echo "\033[31mError deleting Product ID $id:\033[0m " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n\033[32mCleanup completed!\033[0m\n";
    echo "Successfully deleted: \033[1m$deletedCount\033[0m\n";
    if ($errorCount > 0) {
        echo "Errors encountered: \033[31m$errorCount\033[0m\n";
    }
}
