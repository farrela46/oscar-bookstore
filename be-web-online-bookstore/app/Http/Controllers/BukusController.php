<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Image;
use App\Models\Category;
use Illuminate\Support\Str;
use App\Models\BukuCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BukusController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'no_isbn' => 'required|numeric|min:0',
            'judul' => 'required',
            'desc' => 'required',
            'pengarang' => 'required',
            'penerbit' => 'required',
            'tahun_terbit' => 'required',
            'stok' => 'required|numeric|min:0', 
            'harga' => 'required|numeric|min:0', 
            'categoryId' => 'required|array',
            'categoryId.*' => 'exists:categories,id',
        ]);

        try {
            $filePath = null;
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $fileName = time() . '_' . Str::slug($file->getClientOriginalName(), '-') . '.' . $file->getClientOriginalExtension();
                $filePath = $file->storeAs('buku_photos', $fileName);
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

            foreach ($request->input('categoryId') as $category) {
                BukuCategory::create([
                    'category_id' => $category,
                    'buku_id' => $buku->id,
                ]);
            }

            return response()->json([
                'message' => 'Berhasil menambah Buku!',
                'buku' => $buku,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }




    public function update(Request $request, $id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        if ($request->hasFile('foto')) {
            $oldFilePath = str_replace('storage/', 'public/', $buku->foto);
            if (Storage::exists($oldFilePath)) {
                Storage::delete($oldFilePath);
            }

            $file = $request->file('foto');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('buku_photos', $fileName);

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
                $query->orderBy('sold', 'desc');
            })
            ->get()
            ->map(function ($item) {
                $averageRating = DB::table('reviews')
                    ->where('buku_id', $item->id)
                    ->avg('rating');

                $roundedRating = $averageRating ? $this->roundToHalf($averageRating) : 0;

                $categoryNames = $item->categories->pluck('nama')->toArray();

                return [
                    'id' => $item->id,
                    'no_isbn' => $item->no_isbn,
                    'judul' => $item->judul,
                    'desc' => $item->desc,
                    'pengarang' => $item->pengarang,
                    'penerbit' => $item->penerbit,
                    'tahun_terbit' => $item->tahun_terbit,
                    'foto' => asset('storage/buku_photos/' . basename($item->foto)),
                    'stok' => $item->stok,
                    'sold' => $item->sold,
                    'harga' => $item->harga,
                    'slug' => $item->slug,
                    'category' => $categoryNames,
                    'average_rating' => $roundedRating
                ];
            });

        return response()->json($buku);
    }

    /**
     * Round a number to the nearest half (0.5).
     *
     * @param float $number
     * @return float
     */
    private function roundToHalf($number)
    {
        // Round to the nearest half (0.5) but round down if the decimal is less than 0.25
        $rounded = round($number * 2) / 2;
        $decimal = $number - floor($number);

        if ($decimal < 0.25) {
            $rounded = floor($number);
        } elseif ($decimal >= 0.25 && $decimal < 0.75) {
            $rounded = floor($number) + 0.5;
        } else {
            $rounded = ceil($number);
        }

        return $rounded;
    }








    public function getDetailBuku(Request $request, $slug)
    {
        $buku = Buku::with('categories')->where('slug', $slug)->first();

        if (!$buku) {
            return response()->json(['error' => 'Buku not found'], 404);
        }

        $ratingData = DB::table('reviews')
            ->selectRaw('AVG(rating) as average_rating, COUNT(*) as total_reviews')
            ->where('buku_id', $buku->id)
            ->first();

        $roundedRating = $ratingData->average_rating ? $this->roundToHalf($ratingData->average_rating) : 0;

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
            'category' => $categoryNames,
            'average_rating' => $roundedRating,
            'total_reviews' => $ratingData->total_reviews
        ];

        // Return the response as JSON
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
