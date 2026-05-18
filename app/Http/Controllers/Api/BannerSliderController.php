<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BannerSlider;
use Illuminate\Http\Request;

class BannerSliderController extends Controller
{
    public function index()
{
    $banners = BannerSlider::where('is_active', true)
        ->orderBy('sort_order', 'asc')
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($banner) {
            return [
                'id'           => $banner->id,
                'title'        => $banner->title,
                'subtitle'     => $banner->subtitle,
                'image'        => $banner->image ? asset('storage/' . $banner->image) : null,
                'mobile_image' => $banner->mobile_image ? asset('storage/' . $banner->mobile_image) : null,
                'button_text'  => $banner->button_text,
                'button_url'   => $banner->button_url,
                'alt_text'     => $banner->alt_text,
                'description'  => $banner->description,
                'sort_order'   => $banner->sort_order,
                'is_active'    => (bool) $banner->is_active,
            ];
        });

    return response()->json([
        'status'  => true,
        'message' => 'Banner sliders fetched successfully.',
        'data'    => $banners,
    ]);
}
}
