<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\SubCategory;
use App\Models\ProductVariant;
use App\Models\ProductOption;
use App\Models\ProductOptionType;
use App\Models\ProductOptionValue;
use App\Models\VariantOptionValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('products')->truncate();
        DB::table('product_prices')->truncate();
        DB::table('product_variants')->truncate();
        DB::table('product_options')->truncate();
        DB::table('product_option_types')->truncate();
        DB::table('product_option_values')->truncate();
        DB::table('variant_option_values')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $egp = Currency::where('name', 'EGP')->first();
        
        // Create Option Types
        $weightType = ProductOptionType::create(['name' => 'Weight']);
        $packSizeType = ProductOptionType::create(['name' => 'Pack Size']);

        $steaks = SubCategory::where('slug', 'steaks')->first();
        $minced = SubCategory::where('slug', 'minced')->first();
        $roasts = SubCategory::where('slug', 'roasts')->first();
        $wholeChicken = SubCategory::where('slug', 'whole-chicken')->first();
        $fillets = SubCategory::where('slug', 'fillets-and-breasts')->first();
        $chickenAppetizers = SubCategory::where('slug', 'chicken-appetizers')->first();
        $jumboShrimp = SubCategory::where('slug', 'jumbo-shrimp')->first();
        $peeledShrimp = SubCategory::where('slug', 'peeled-shrimp')->first();
        $butterfliedShrimp = SubCategory::where('slug', 'butterflied-shrimp')->first();

        $products = [
            // Meat - Steaks (WITH VARIANTS - Weight)
            [
                'name_en' => 'Ribeye Steak',
                'name_ar' => 'ريب آي ستيك',
                'description_en' => 'Premium grain-fed beef ribeye, perfectly marbled for maximum flavor.',
                'description_ar' => 'ريب آي بقري فاخر مغذى على الحبوب، معرق بشكل مثالي لأقصى قدر من النكهة.',
                'sub_category_id' => $steaks->id,
                'category_id' => $steaks->category_id,
                'sku' => 'MT-STR-001',
                'has_variants' => true,
                'variants' => [
                    ['weight' => '250g', 'price' => 450, 'sku' => 'MT-STR-001-250'],
                    ['weight' => '500g', 'price' => 850, 'sku' => 'MT-STR-001-500'],
                    ['weight' => '1kg', 'price' => 1600, 'sku' => 'MT-STR-001-1KG'],
                ],
                'option_type_id' => $weightType->id,
            ],
            [
                'name_en' => 'Wagyu Striploin',
                'name_ar' => 'ستيك واجيو ستربلويين',
                'description_en' => 'Authentic Wagyu beef with exceptional tenderness and buttery texture.',
                'description_ar' => 'لحم واجيو أصلي بليونة استثنائية وقوام زبدي.',
                'sub_category_id' => $steaks->id,
                'category_id' => $steaks->category_id,
                'sku' => 'MT-STR-002',
                'has_variants' => true,
                'variants' => [
                    ['weight' => '200g', 'price' => 1200, 'sku' => 'MT-STR-002-200'],
                    ['weight' => '400g', 'price' => 2300, 'sku' => 'MT-STR-002-400'],
                ],
                'option_type_id' => $weightType->id,
            ],
            [
                'name_en' => 'T-Bone Steak',
                'name_ar' => 'تي بون ستيك',
                'description_en' => 'Classic T-Bone cut combining the tenderloin and strip steak.',
                'description_ar' => 'قطعة تي بون كلاسيكية تجمع بين التندرلوين والستريب ستيك.',
                'sub_category_id' => $steaks->id,
                'category_id' => $steaks->category_id,
                'price' => 550,
                'sku' => 'MT-STR-003',
                'has_variants' => false,
            ],

            // Meat - Minced
            [
                'name_en' => 'Premium Beef Burger',
                'name_ar' => 'برجر لحم فاخر',
                'description_en' => 'House-blend minced beef, perfect for juicy gourmet burgers.',
                'description_ar' => 'مزيج لحم بقري مفروم خاص، مثالي لبرجر جورميه عصاري.',
                'sub_category_id' => $minced->id,
                'category_id' => $minced->category_id,
                'sku' => 'MT-MNC-001',
                'has_variants' => true,
                'variants' => [
                    ['size' => '4 Pieces', 'price' => 380, 'sku' => 'MT-MNC-001-4P'],
                    ['size' => '8 Pieces', 'price' => 720, 'sku' => 'MT-MNC-001-8P'],
                ],
                'option_type_id' => $packSizeType->id,
            ],
            [
                'name_en' => 'Lean Minced Beef',
                'name_ar' => 'لحم بقري مفروم قليل الدسم',
                'description_en' => 'High-quality lean beef, minced daily for freshness.',
                'description_ar' => 'لحم بقري قليل الدسم عالي الجودة، يفرم يومياً لضمان الطزاجة.',
                'sub_category_id' => $minced->id,
                'category_id' => $minced->category_id,
                'price' => 350,
                'sku' => 'MT-MNC-002',
                'has_variants' => false,
            ],

            // Meat - Roasts
            [
                'name_en' => 'Beef Roast Joint',
                'name_ar' => 'عرق روستو بقري',
                'description_en' => 'Perfect for slow roasting, tender and full of flavor.',
                'description_ar' => 'مثالي للطهي البطيء، لين ومليء بالنكهة.',
                'sub_category_id' => $roasts->id,
                'category_id' => $roasts->category_id,
                'sku' => 'MT-RST-001',
                'has_variants' => true,
                'variants' => [
                    ['weight' => '1kg', 'price' => 480, 'sku' => 'MT-RST-001-1KG'],
                    ['weight' => '1.5kg', 'price' => 700, 'sku' => 'MT-RST-001-1.5KG'],
                    ['weight' => '2kg', 'price' => 920, 'sku' => 'MT-RST-001-2KG'],
                ],
                'option_type_id' => $weightType->id,
            ],
            [
                'name_en' => 'Beef Cubes for Stew',
                'name_ar' => 'مكعبات لحم للطبخ',
                'description_en' => 'Hand-cut beef cubes, ideal for traditional stews and tagines.',
                'description_ar' => 'مكعبات لحم بقري مقطعة يدوياً، مثالية لليخنات والطواجن التقليدية.',
                'sub_category_id' => $roasts->id,
                'category_id' => $roasts->category_id,
                'price' => 420,
                'sku' => 'MT-RST-002',
                'has_variants' => false,
            ],

            // Chicken - Whole
            [
                'name_en' => 'Farm Fresh Whole Chicken',
                'name_ar' => 'دجاج كامل طازج',
                'description_en' => 'Antibiotic-free, farm-raised whole chicken.',
                'description_ar' => 'دجاج كامل مربى في المزارع، خالي من المضادات الحيوية.',
                'sub_category_id' => $wholeChicken->id,
                'category_id' => $wholeChicken->category_id,
                'sku' => 'CH-WHL-001',
                'has_variants' => true,
                'variants' => [
                    ['weight' => '1kg', 'price' => 180, 'sku' => 'CH-WHL-001-1KG'],
                    ['weight' => '1.2kg', 'price' => 210, 'sku' => 'CH-WHL-001-1.2KG'],
                    ['weight' => '1.4kg', 'price' => 240, 'sku' => 'CH-WHL-001-1.4KG'],
                ],
                'option_type_id' => $weightType->id,
            ],

            // Chicken - Fillets
            [
                'name_en' => 'Fresh Chicken Breast',
                'name_ar' => 'صدور دجاج طازجة',
                'description_en' => 'Skinless and boneless chicken breasts, lean and tender.',
                'description_ar' => 'صدور دجاج بدون جلد وعظم، قليلة الدسم ولينة.',
                'sub_category_id' => $fillets->id,
                'category_id' => $fillets->category_id,
                'price' => 220,
                'sku' => 'CH-FIL-001',
                'has_variants' => false,
            ],
            [
                'name_en' => 'Chicken Thighs (Shish Taouk)',
                'name_ar' => 'أوراك دجاج (شيش طاووق)',
                'description_en' => 'Juicy chicken thighs, hand-cut for the perfect Shish Taouk.',
                'description_ar' => 'أوراك دجاج عصارية، مقطعة يدوياً لتحضير أفضل شيش طاووق.',
                'sub_category_id' => $fillets->id,
                'category_id' => $fillets->category_id,
                'price' => 210,
                'sku' => 'CH-FIL-002',
                'has_variants' => false,
            ],

            // Chicken - Appetizers
            [
                'name_en' => 'Marinated Chicken Wings',
                'name_ar' => 'أجنحة دجاج متبلة',
                'description_en' => 'Spicy Buffalo style marinated chicken wings.',
                'description_ar' => 'أجنحة دجاج متبلة على طريقة بافلو الحارة.',
                'sub_category_id' => $chickenAppetizers->id,
                'category_id' => $chickenAppetizers->category_id,
                'price' => 150,
                'sku' => 'CH-APP-001',
                'has_variants' => false,
            ],
            [
                'name_en' => 'Crispy Chicken Strips',
                'name_ar' => 'ستربس دجاج مقرمش',
                'description_en' => 'Breaded chicken breast strips, ready to fry.',
                'description_ar' => 'شرائح صدور دجاج مغطاة بالبقسماط، جاهزة للقلي.',
                'sub_category_id' => $chickenAppetizers->id,
                'category_id' => $chickenAppetizers->category_id,
                'sku' => 'CH-APP-002',
                'has_variants' => true,
                'variants' => [
                    ['weight' => '500g', 'price' => 190, 'sku' => 'CH-APP-002-500'],
                    ['weight' => '1kg', 'price' => 360, 'sku' => 'CH-APP-002-1KG'],
                ],
                'option_type_id' => $weightType->id,
            ],

            // Shrimp - Jumbo
            [
                'name_en' => 'Jumbo Red Shrimp',
                'name_ar' => 'جمبري أحمر جامبو',
                'description_en' => 'Extra-large sea-caught red shrimp, perfect for grilling.',
                'description_ar' => 'جمبري أحمر جامبو من البحر، مثالي للشواء.',
                'sub_category_id' => $jumboShrimp->id,
                'category_id' => $jumboShrimp->category_id,
                'sku' => 'SH-JMB-001',
                'has_variants' => true,
                'variants' => [
                    ['weight' => '500g', 'price' => 750, 'sku' => 'SH-JMB-001-500'],
                    ['weight' => '1kg', 'price' => 1400, 'sku' => 'SH-JMB-001-1KG'],
                ],
                'option_type_id' => $weightType->id,
            ],

            // Shrimp - Peeled
            [
                'name_en' => 'Peeled Medium Shrimp',
                'name_ar' => 'جمبري وسط مقشر',
                'description_en' => 'Cleaned and peeled medium shrimp, ready for cooking.',
                'description_ar' => 'جمبري وسط منظف ومقشر، جاهز للطبخ.',
                'sub_category_id' => $peeledShrimp->id,
                'category_id' => $peeledShrimp->category_id,
                'price' => 450,
                'sku' => 'SH-PEL-001',
                'has_variants' => false,
            ],
            [
                'name_en' => 'Peeled Baby Shrimp',
                'name_ar' => 'جمبري صغير مقشر',
                'description_en' => 'Small peeled shrimp, ideal for pasta and rice dishes.',
                'description_ar' => 'جمبري صغير مقشر، مثالي لأطباق المكرونة والأرز.',
                'sub_category_id' => $peeledShrimp->id,
                'category_id' => $peeledShrimp->category_id,
                'price' => 350,
                'sku' => 'SH-PEL-002',
                'has_variants' => false,
            ],

            // Shrimp - Butterflied
            [
                'name_en' => 'Butterflied Garlic Shrimp',
                'name_ar' => 'جمبري بترفلاي بالثوم',
                'description_en' => 'Butterflied shrimp marinated in garlic and herbs.',
                'description_ar' => 'جمبري بترفلاي متبل بالثوم والأعشاب.',
                'sub_category_id' => $butterfliedShrimp->id,
                'category_id' => $butterfliedShrimp->category_id,
                'sku' => 'SH-BTF-001',
                'has_variants' => true,
                'variants' => [
                    ['weight' => '400g', 'price' => 520, 'sku' => 'SH-BTF-001-400'],
                    ['weight' => '800g', 'price' => 980, 'sku' => 'SH-BTF-001-800'],
                ],
                'option_type_id' => $weightType->id,
            ],

            // More products to reach 20
            [
                'name_en' => 'Beef Fillet Mignon',
                'name_ar' => 'فيليه مينيون بقري',
                'description_en' => 'The most tender cut of beef, grain-fed and aged.',
                'description_ar' => 'أكثر قطع اللحم ليونة، مغذى على الحبوب ومعتق.',
                'sub_category_id' => $steaks->id,
                'category_id' => $steaks->category_id,
                'price' => 600,
                'sku' => 'MT-STR-004',
                'has_variants' => false,
            ],
            [
                'name_en' => 'Beef Kofta Blend',
                'name_ar' => 'خلطة كفتة بقري',
                'description_en' => 'Traditional Egyptian kofta blend with secret spices.',
                'description_ar' => 'خلطة الكفتة المصرية التقليدية مع بهارات سرية.',
                'sub_category_id' => $minced->id,
                'category_id' => $minced->category_id,
                'price' => 360,
                'sku' => 'MT-MNC-003',
                'has_variants' => false,
            ],
            [
                'name_en' => 'Whole Duck',
                'name_ar' => 'بط كامل',
                'description_en' => 'Premium farm-raised duck, perfect for roasting.',
                'description_ar' => 'بط مزارع فاخر، مثالي للتحمير.',
                'sub_category_id' => $wholeChicken->id,
                'category_id' => $wholeChicken->category_id,
                'price' => 280,
                'sku' => 'CH-WHL-002',
                'has_variants' => false,
            ],
            [
                'name_en' => 'Seafood Mix',
                'name_ar' => 'فواكه البحر مشكلة',
                'description_en' => 'A mix of shrimp, calamari, and fish pieces.',
                'description_ar' => 'تشكيلة من الجمبري والكاليماري وقطع السمك.',
                'sub_category_id' => $peeledShrimp->id,
                'category_id' => $peeledShrimp->category_id,
                'price' => 400,
                'sku' => 'SH-PEL-003',
                'has_variants' => false,
            ],
        ];

        foreach ($products as $p) {
            $product = Product::create([
                'name_en' => $p['name_en'],
                'name_ar' => $p['name_ar'],
                'description_en' => $p['description_en'],
                'description_ar' => $p['description_ar'],
                'sub_category_id' => $p['sub_category_id'],
                'category_id' => $p['category_id'],
                'sku' => $p['sku'],
                'quantity' => 100,
                'is_active' => true,
                'has_variants' => $p['has_variants'],
            ]);

            if ($p['has_variants']) {
                // Create Product Option
                $option = ProductOption::create([
                    'product_id' => $product->id,
                    'product_option_type_id' => $p['option_type_id'],
                    'order' => 1,
                ]);

                foreach ($p['variants'] as $v) {
                    $valStr = $v['weight'] ?? $v['size'];
                    
                    // Create Option Value
                    $optionValue = ProductOptionValue::create([
                        'product_option_id' => $option->id,
                        'value' => $valStr,
                        'standard_value' => $valStr,
                        'order' => 1,
                    ]);

                    // Create Product Variant
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $v['sku'],
                        'price' => $v['price'],
                        'quantity' => 50,
                        'is_active' => true,
                    ]);

                    // Link Variant to Option Value
                    VariantOptionValue::create([
                        'product_variant_id' => $variant->id,
                        'product_option_value_id' => $optionValue->id,
                    ]);
                }
            } else {
                ProductPrice::create([
                    'product_id' => $product->id,
                    'currency_id' => $egp->id,
                    'price' => $p['price'],
                    'price_after_discount' => 0,
                ]);
            }
        }
    }
}
