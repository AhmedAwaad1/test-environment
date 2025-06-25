<?php

namespace App\Http\Services\Dshboard;

use App\Repositories\Dashboard\DashboardRepository;
use Illuminate\Support\Facades\Response;

class DashboardService
{
    protected $dashboardRepo;

    public function __construct(DashboardRepository $dashboardRepo)
    {
        $this->dashboardRepo = $dashboardRepo;
    }

    public function getStatistics()
    {
        try {
            $stats = $this->dashboardRepo->getStats();

            return Response::successResponse($stats, 'Dashboard statistics loaded successfully');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to load dashboard statistics');
        }
    }
}