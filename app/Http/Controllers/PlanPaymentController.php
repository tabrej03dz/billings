<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanPaymentController extends Controller
{
    public function show(Plan $plan)
    {
        return view('plans.payment', compact('plan'));
    }
}
