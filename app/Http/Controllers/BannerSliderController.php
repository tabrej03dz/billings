<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BannerSlider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerSliderController extends Controller
{
    public function index()
    {
        $banners = BannerSlider::orderBy('sort_order', 'asc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.banner-sliders.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banner-sliders.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => ['nullable', 'string', 'max:255'],
            'subtitle'     => ['nullable', 'string', 'max:255'],
            'image'        => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'button_text'  => ['nullable', 'string', 'max:255'],
            'button_url'   => ['nullable', 'string', 'max:500'],
            'alt_text'     => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request->file('image'), 'banner-sliders');
        }

        if ($request->hasFile('mobile_image')) {
            $data['mobile_image'] = $this->uploadImage($request->file('mobile_image'), 'banner-sliders/mobile');
        }

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        BannerSlider::create($data);

        return redirect()
            ->route('admin.banner-sliders.index')
            ->with('success', 'Banner slider created successfully.');
    }

    public function show(BannerSlider $bannerSlider)
    {
        return view('admin.banner-sliders.show', compact('bannerSlider'));
    }

    public function edit(BannerSlider $bannerSlider)
    {
        return view('admin.banner-sliders.edit', compact('bannerSlider'));
    }

    public function update(Request $request, BannerSlider $bannerSlider)
    {
        $data = $request->validate([
            'title'        => ['nullable', 'string', 'max:255'],
            'subtitle'     => ['nullable', 'string', 'max:255'],
            'image'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'mobile_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'button_text'  => ['nullable', 'string', 'max:255'],
            'button_url'   => ['nullable', 'string', 'max:500'],
            'alt_text'     => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'sort_order'   => ['nullable', 'integer', 'min:0'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        if ($request->hasFile('image')) {
            $this->deleteImage($bannerSlider->image);
            $data['image'] = $this->uploadImage($request->file('image'), 'banner-sliders');
        }

        if ($request->hasFile('mobile_image')) {
            $this->deleteImage($bannerSlider->mobile_image);
            $data['mobile_image'] = $this->uploadImage($request->file('mobile_image'), 'banner-sliders/mobile');
        }

        if ($request->has('remove_mobile_image')) {
            $this->deleteImage($bannerSlider->mobile_image);
            $data['mobile_image'] = null;
        }

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');
        $data['updated_by'] = Auth::id();

        $bannerSlider->update($data);

        return redirect()
            ->route('admin.banner-sliders.index')
            ->with('success', 'Banner slider updated successfully.');
    }

    public function destroy(BannerSlider $bannerSlider)
    {
        $this->deleteImage($bannerSlider->image);
        $this->deleteImage($bannerSlider->mobile_image);

        $bannerSlider->delete();

        return redirect()
            ->route('admin.banner-sliders.index')
            ->with('success', 'Banner slider deleted successfully.');
    }

    public function toggleStatus(BannerSlider $bannerSlider)
    {
        $bannerSlider->update([
            'is_active' => !$bannerSlider->is_active,
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Banner slider status updated successfully.');
    }

    private function uploadImage($file, $folder)
    {
        $filename = time() . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($folder, $filename, 'public');
    }

    private function deleteImage($path)
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
