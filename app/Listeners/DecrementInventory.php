<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Http\Services\Product\StockService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DecrementInventory
{
    protected $stockService;

    /**
     * Create the event listener.
     */
    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPlaced $event): void
    {
        $this->stockService->decrementStockFromCart($event->cart);
    }
}
