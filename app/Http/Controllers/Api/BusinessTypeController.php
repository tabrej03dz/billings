<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BusinessType;

class BusinessTypeController extends Controller
{
    public function index(){
        $businessTypes = BusinessType::all();
        return response()->json($businessTypes);
    }
}
