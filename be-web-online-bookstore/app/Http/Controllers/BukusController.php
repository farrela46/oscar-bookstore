<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\BukuCategory;
use App\Models\Image;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class BukusController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'foto' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'no_isbn' => 'required',
            'judul' => 'required',
            'desc' => 'required',
            'pengarang' => 'required',
            'penerbit' => 'required',
            'tahun_terbit' => 'required',
            'stok' => 'required',
            'harga' => 'required',
            'categoryId' => 'required',
        ]);

        try {
            $filePath = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $fileName = time() . '_' . Str::slug($file->getClientOriginalName(), '-') . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('public/buku_photos', $fileName);
            }

            $buku = Buku::create([
                'no_isbn' => $request->input('no_isbn'),
                'judul' => $request->input('judul'),
                'desc' => $request->input('desc'),
                'pengarang' => $request->input('pengarang'),
                'penerbit' => $request->input('penerbit'),
                'tahun_terbit' => $request->input('tahun_terbit'),
                'foto' => $filePath,
                'stok' => $request->input('stok'),
                'harga' => $request->input('harga'),
                'slug' => Str::slug($request->input('judul')),
            ]);

            foreach ($request->input('categoryId') as $key => $category) {
                BukuCategory::insert(
                    [
                        'category_id' => $category,
                        'buku_id' => $buku->id
                    ]
                );
            }

            return response()->json([
                'message' => 'Successfully Add Buku!',
                'buku' => $buku,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function saveImage(array|UploadedFile $file, int $id): void
    {
        $file->store('buku_photos');
        $path = Storage::path('buku_photos/' . $file->hashName());
        $url = Storage::url('buku_photos/' . $file->hashName());
        $this->replaceImage([
            'buku_id' => $id,
            'new_path' => $path,
            'new_url' => $url
        ]);
    }

    private function replaceImage(array $data): void
    {
        $buku = $data['buku_id'];
        Image::create([
            'file_path' => $data['new_path'],
            'buku_id' => $buku
        ]);
    }

    public function update(Request $request, $id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        if ($request->hasFile('foto')) {
            if (Storage::exists($buku->foto)) {
                Storage::delete($buku->foto);
            }

            $file = $request->file('foto');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('public/buku_photos', $fileName);

            $buku->foto = 'storage/buku_photos/' . $fileName;
        }

        $buku->no_isbn = $request->input('no_isbn');
        $buku->judul = $request->input('judul');
        $buku->desc = $request->input('desc');
        $buku->pengarang = $request->input('pengarang');
        $buku->penerbit = $request->input('penerbit');
        $buku->tahun_terbit = $request->input('tahun_terbit');
        $buku->stok = $request->input('stok');
        $buku->harga = $request->input('harga');
        $buku->save();

        $categories = json_decode($request->input('categories'), true);
        $categoryIds = Category::whereIn('nama', $categories)->pluck('id')->toArray();
        $buku->categories()->sync($categoryIds);

        return response()->json(['message' => 'Successfully updated Buku'], 200);
    }


    // public function getBuku(Request $request)
    // {
    //     $buku = Buku::with('categories')->get();

    //     $bukuData = $buku->map(function ($item) {
    //         $categoryNames = $item->categories->pluck('nama')->toArray();

    //         return [
    //             'id' => $item->id,
    //             'no_isbn' => $item->no_isbn,
    //             'judul' => $item->judul,
    //             'desc' => $item->desc,
    //             'pengarang' => $item->pengarang,
    //             'penerbit' => $item->penerbit,
    //             'tahun_terbit' => $item->tahun_terbit,
    //             'foto' => asset('storage/buku_photos/' . basename($item->foto)), // Adjust the path as needed
    //             'stok' => $item->stok,
    //             'sold' => $item->sold,
    //             'harga' => $item->harga,
    //             'slug' => $item->slug,
    //             'category' => $categoryNames,
    //         ];
    //     });

    //     return response()->json($bukuData);
    // }

    public function getBuku(Request $request)
    {
        $search = $request->input('search', '');
        $sortBy = $request->input('sortBy', '');

        $buku = Buku::with('categories')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('judul', 'like', "%{$search}%")
                        ->orWhere('pengarang', 'like', "%{$search}%")
                        ->orWhere('penerbit', 'like', "%{$search}%")
                        ->orWhereHas('categories', function ($query) use ($search) {
                            $query->where('nama', 'like', "%{$search}%");
                        });
                });
            })
            ->when($sortBy, function ($query, $sortBy) {
                if ($sortBy === 'lowstock') {
                    $query->orderBy('stok', 'asc');
                } elseif ($sortBy === 'mostsold') {
                    $query->orderBy('sold', 'desc');
                }
            }, function ($query) {
                // Default sort by most sold if no sortBy is provided
                $query->orderBy('sold', 'desc');
            })
            ->get();

        $bukuData = $buku->map(function ($item) {
            $categoryNames = $item->categories->pluck('nama')->toArray();

            return [
                'id' => $item->id,
                'no_isbn' => $item->no_isbn,
                'judul' => $item->judul,
                'desc' => $item->desc,
                'pengarang' => $item->pengarang,
                'penerbit' => $item->penerbit,
                'tahun_terbit' => $item->tahun_terbit,
                'foto' => asset('storage/buku_photos/' . basename($item->foto)), // Adjust the path as needed
                'stok' => $item->stok,
                'sold' => $item->sold,
                'harga' => $item->harga,
                'slug' => $item->slug,
                'category' => $categoryNames,
            ];
        });

        return response()->json($bukuData);
    }




    public function getDetailBuku(Request $request, $slug)
    {
        $buku = Buku::with('categories')->where('slug', $slug)->first();

        if (!$buku) {
            return response()->json(['error' => 'Buku not found'], 404);
        }
        $categoryNames = $buku->categories->pluck('nama')->implode(', ');

        $bukuData = [
            'id' => $buku->id,
            'no_isbn' => $buku->no_isbn,
            'judul' => $buku->judul,
            'desc' => $buku->desc,
            'pengarang' => $buku->pengarang,
            'penerbit' => $buku->penerbit,
            'tahun_terbit' => $buku->tahun_terbit,
            'foto' => asset('storage/buku_photos/' . basename($buku->foto)),
            'stok' => $buku->stok,
            'sold' => $buku->sold,
            'harga' => $buku->harga,
            'category' => $categoryNames
        ];
        return response()->json($bukuData);
    }


    public function delete($id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json(['success' => false, 'message' => 'Buku not found'], 404);
        }

        if (Storage::exists($buku->foto)) {
            Storage::delete($buku->foto);
        }

        $buku->delete();

        return response()->json(['success' => true, 'message' => 'Buku deleted successfully'], 200);
    }



}