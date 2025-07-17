<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Services\Dshboard\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function getStatistics()
    {
        return $this->dashboardService->getStatistics();
    }

    public function getOrderStatistics()
    {
        return $this->dashboardService->getOrderStatistics();
    }
}
