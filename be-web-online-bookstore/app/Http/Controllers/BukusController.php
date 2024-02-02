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
        // Validate the request
        $request->validate([
            'foto' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'no_isbn' => 'required',
            'judul' => 'required',
            'desc' => 'required',
            'pengarang' => 'required',
            'penerbit' => 'required',
            'tahun_terbit' => 'required',
            'stok' => 'required',
        ]);

        // Handle file upload
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            // $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $fileName = time() . '_' . Str::of($file->getClientOriginalName())->slug('-') . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('public/buku_photos', $fileName);

            // Save the file path to the database
            $created = Buku::create([
                'no_isbn' => $request->input('no_isbn'),
                'judul' => $request->input('judul'),
                'desc' => $request->input('desc'),
                'pengarang' => $request->input('pengarang'),
                'penerbit' => $request->input('penerbit'),
                'tahun_terbit' => $request->input('tahun_terbit'),
                'foto' => $filePath, // Save the file path
                'stok' => $request->input('stok'),
            ]);

            if ($created) {
                return response()->json([
                    'message' => 'Successfully Add Buku!',
                ], 201);
            } else {
                return response()->json([
                    'message' => 'Server error',
                ], 500);
            }
        }

        return response()->json([
            'message' => 'File not provided',
        ], 400);
    }

    public function getBuku(Request $request)
    {
        $buku = Buku::all();

        if ($buku->isEmpty()) {
            return response()->json(['error' => 'Buku tidak ditemukan'], 404);
        }

        // Transform the data to include the file URL
        $bukuData = $buku->map(function ($item) {
            return [
                'no_isbn' => $item->no_isbn,
                'judul' => $item->judul,
                'desc' => $item->desc,
                'pengarang' => $item->pengarang,
                'penerbit' => $item->penerbit,
                'tahun_terbit' => $item->tahun_terbit,
                'foto' => asset('storage/buku_photos/' . basename($item->foto)), // Adjust the path as needed
                'stok' => $item->stok,
            ];
        });

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
