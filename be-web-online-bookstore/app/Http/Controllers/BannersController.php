<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannersController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'foto' => 'required|image|max:2048', // image file validation
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $fileName = time() . '_' . Str::slug($file->getClientOriginalName(), '-') . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('buku_banners', $fileName);

            $banner = Banner::create([
                'judul' => $request->input('judul'),
                'path' => $filePath,
            ]);

            return response()->json($banner, 201);
        }

        return response()->json(['error' => 'File upload failed'], 422);
    }

    public function index()
    {
        $banners = Banner::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'judul' => $item->judul,
                'foto' => asset('storage/buku_banners/' . basename($item->path)),
            ];
        });

        return response()->json($banners);
    }

    public function destroy($id)
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json(['error' => 'Banner not found'], 404);
        }

        if (Storage::exists($banner->path)) {
            Storage::delete($banner->path);
        }

        $banner->delete();

        return response()->json(['message' => 'Banner deleted successfully']);
    }

}

