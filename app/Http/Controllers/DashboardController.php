<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        /*
        |--------------------------------------------------------------------------
        | Super Admin Dashboard
        |--------------------------------------------------------------------------
        |
        | Project me kuch jagah role "super admin" aur kuch purane code me
        | "super_admin" use hua hai. Dono ko support kar rahe hain.
        |
        */
        if ($user->hasAnyRole(['super admin', 'super_admin'])) {
            return app(SuperAdminDashboardController::class)->index($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Normal Business Dashboard
        |--------------------------------------------------------------------------
        |
        | Existing business-wise dashboard ko bilkul same rakha gaya hai.
        |
        */
        return app(BusinessDashboardController::class)->index($request);
    }
}