<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannersController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'image' => 'required|image|max:2048', // image file validation
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . Str::slug($file->getClientOriginalName(), '-') . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('public/buku_banners', $fileName);

            $banner = Banner::create([
                'judul' => $request->input('judul'),
                'image_path' => $filePath,
            ]);

            return response()->json($banner, 201);
        }

        return response()->json(['error' => 'File upload failed'], 422);
    }

    public function index()
    {
        $banners = Banner::all();
        return response()->json($banners);
    }

}

