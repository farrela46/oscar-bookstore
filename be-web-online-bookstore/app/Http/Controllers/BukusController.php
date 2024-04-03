<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            // $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $fileName = time() . '_' . Str::of($file->getClientOriginalName())->slug('-') . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('public/buku_photos', $fileName);

            $created = Buku::create([
                'no_isbn' => $request->input('no_isbn'),
                'judul' => $request->input('judul'),
                'desc' => $request->input('desc'),
                'pengarang' => $request->input('pengarang'),
                'penerbit' => $request->input('penerbit'),
                'tahun_terbit' => $request->input('tahun_terbit'),
                'foto' => $filePath, // Save the file path
                'stok' => $request->input('stok'),
                'harga' => $request->input('harga'),
            ]);

            if ($created) {
                $categories = $request->input('categories');
                $created->categories()->attach($categories);
            }

            return response()->json([
                'message' => $created ? 'Successfully Add Buku!' : 'Server error',
            ], $created ? 201 : 500);
        }

        return response()->json([
            'message' => 'File not provided',
        ], 400);
    }

    public function update(Request $request, $id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json(['message' => 'Buku tidak ditemukan'], 404);
        }

        // Process file upload if a new file is provided
        if ($request->hasFile('foto')) {
            if (Storage::exists($buku->foto)) {
                Storage::delete($buku->foto);
            }

            $file = $request->file('foto');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('public/buku_photos', $fileName);

            $buku->foto = $filePath;
        }

        // Update the book properties
        $buku->no_isbn = $request->input('no_isbn');
        $buku->judul = $request->input('judul');
        $buku->desc = $request->input('desc');
        $buku->pengarang = $request->input('pengarang');
        $buku->penerbit = $request->input('penerbit');
        $buku->tahun_terbit = $request->input('tahun_terbit');
        $buku->stok = $request->input('stok');
        $buku->harga = $request->input('harga');
        $buku->save();
        
        $categories = $request->input('categories');
        $buku->categories()->sync($categories);
        
        return response()->json(['message' => 'Successfully updated Buku'], 200);
    }
    public function getBuku(Request $request)
    {
        $buku = Buku::all();

        if ($buku->isEmpty()) {
            return response()->json(['error' => 'Buku tidak ditemukan'], 404);
        }

        // Transform the data to include the file URL and id
        $bukuData = $buku->map(function ($item) {
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
                'harga' => $item->harga,
            ];
        });

        return response()->json($bukuData);
    }
    public function getDetailBuku(Request $request, $id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json(['error' => 'Buku tidak ditemukan'], 404);
        }

        // Construct the foto path in the same way as getBuku function
        $bukuData = [
            'id' => $buku->id,
            'no_isbn' => $buku->no_isbn,
            'judul' => $buku->judul,
            'desc' => $buku->desc,
            'pengarang' => $buku->pengarang,
            'penerbit' => $buku->penerbit,
            'tahun_terbit' => $buku->tahun_terbit,
            'foto' => asset('storage/buku_photos/' . basename($buku->foto)), // Use the same path construction
            'stok' => $buku->stok,
            'harga' => $buku->harga,
        ];

        return response()->json($bukuData);
    }
    public function delete($id)
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return response()->json(['success' => false, 'message' => 'Buku not found'], 404);
        }

        // Delete the associated file
        if (Storage::exists($buku->foto)) {
            Storage::delete($buku->foto);
        }

        $buku->delete();

        return response()->json(['success' => true, 'message' => 'Buku deleted successfully'], 200);
    }
}
