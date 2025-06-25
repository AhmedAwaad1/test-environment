<?php

namespace App\Repositories\Dashboard;

use App\Models\Contact;
use App\Models\Product;
use App\Models\Review;
use App\Models\Testimonial;

class DashboardRepository
{
    public function getStats()
    {
        return [
            'contacts'     => Contact::count(),
            'reviews'      => Review::count(),
            'products'     => Product::count(),
//            'testimonials' => Testimonial::count(),
        ];
    }
}