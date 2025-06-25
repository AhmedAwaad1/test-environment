<?php

namespace App\Http\Controllers\Currency;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        $currencies = Currency::all();
        return response()->json([
            'status' => true,
            'message' => 'Currencies retrieved successfully',
            'data' => $currencies,
        ]);
    }
}
