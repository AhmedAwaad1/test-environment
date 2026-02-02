<?php

namespace App\Console\Commands;

use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class OptimizeExistingImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-existing-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate WebP, Medium, and Small versions of product images';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $images = ProductImage::all();
        $this->info("Found " . $images->count() . " images to process.");

        $bar = $this->output->createProgressBar($images->count());
        $bar->start();

        foreach ($images as $productImage) {
            $originalPath = $productImage->getRawOriginal('image');

            if (!$originalPath || !Storage::disk('public')->exists($originalPath)) {
                $bar->advance();
                continue;
            }

            $fullPath = Storage::disk('public')->path($originalPath);
            $directory = dirname($originalPath);
            $filename = pathinfo($originalPath, PATHINFO_FILENAME);

            try {
                // 1. WebP Version
                $webpPath = $directory . '/' . $filename . '.webp';
                if (!Storage::disk('public')->exists($webpPath)) {
                    $img = Image::make($fullPath)->encode('webp', 80);
                    Storage::disk('public')->put($webpPath, (string) $img);
                }
                $productImage->image_webp = $webpPath;

                // 2. Medium Version (600x600)
                $mediumPath = $directory . '/' . $filename . '_medium.webp';
                if (!Storage::disk('public')->exists($mediumPath)) {
                    $img = Image::make($fullPath)
                        ->resize(600, 600, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->encode('webp', 80);
                    Storage::disk('public')->put($mediumPath, (string) $img);
                }
                $productImage->image_medium = $mediumPath;

                // 3. Small Version (300x300)
                $smallPath = $directory . '/' . $filename . '_small.webp';
                if (!Storage::disk('public')->exists($smallPath)) {
                    $img = Image::make($fullPath)
                        ->resize(300, 300, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        })
                        ->encode('webp', 80);
                    Storage::disk('public')->put($smallPath, (string) $img);
                }
                $productImage->image_small = $smallPath;

                $productImage->save();
            } catch (\Exception $e) {
                $this->error("\nError processing image ID {$productImage->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info("\nOptimization completed!");
    }
}
