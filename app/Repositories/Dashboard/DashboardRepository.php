<?php

namespace App\Repositories\Dashboard;

use App\Models\Contact;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\Testimonial;

class DashboardRepository
{
    public function getStats()
    {
        $now = now();

        $monthlyRevenue = Order::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(subtotal + shipping_price) as total')
            ->where('status', 'delivered')
            ->where('created_at', '>=', $now->copy()->subMonths(5)->startOfMonth()) // آخر 6 شهور
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i)->format('Y-m');
            $months->put($month, (float) ($monthlyRevenue[$month] ?? 0));
        }

        return [
            'contacts'        => Contact::count(),
            'reviews'         => Review::count(),
            'products'        => Product::count(),
            'total_revenue'   => $months->sum(),
            'monthly_revenue' => $months,
        ];
    }


    public function getOrderStatistics()
    {
        $firstDayOfMonth = now()->startOfMonth();
        $lastDayOfMonth = now()->endOfMonth();

        $orders = Order::whereBetween('created_at', [$firstDayOfMonth, $lastDayOfMonth]);
        $subtotalRevenue = (clone $orders)->where('status', 'delivered')->sum('subtotal');
        $shippingTotal = (clone $orders)->where('status', 'delivered')->sum('shipping_price');
        $totalRevenue = $subtotalRevenue + $shippingTotal;

        $totalOrders = (clone $orders)->count();

        $statusCounts = (clone $orders)->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'subtotal_revenue' => $subtotalRevenue,
            'shipping_total' => $shippingTotal,
            'total_orders' => $totalOrders,
            'total_revenue'    => $totalRevenue,
            'delivered_orders' => $statusCounts['delivered'] ?? 0,
            'canceled_orders' => $statusCounts['canceled'] ?? 0,
        ];
    }
}
