<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressesController extends Controller
{
    public function store(Request $request)
    {
        $created = Address::create([
            'nama_penerima' => $request->input('nama_penerima'),
            'no_telp_penerima' => $request->input('no_telp_penerima'),
            'jalan' => $request->input('jalan'),
            'kelurahan' => $request->input('kelurahan'),
            'kecamatan' => $request->input('kecamatan'),
            'kota' => $request->input('kota'),
            'provinsi' => $request->input('provinsi'),
            'kode_pos' => $request->input('kode_pos'),
            'user_id' => $request->user()->id
        ]);

        if ($created) {
            return response()->json([
                'message' => 'Successfuly Add Address!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'Server error'
            ], 500);
        }
    }

    public function getAddress(Request $request)
    {
        $address = Address::all();

        if (!$address) {
            return response()->json(['error' => 'Address not found'], 404);
        }

        return response()->json($address);
    }

    public function getById(Request $request, $id)
    {
        
        $address = Address::where('user_id', $id)->get();

        if (!$address) {
            return response()->json(['error' => 'Address not found'], 404);
        }

        return response()->json($address);
    }

    public function update(Request $request, $id)
{
    $address = Address::find($id);

    if (!$address) {
        return response()->json(['message' => 'Address not found'], 404);
    }

    $address->update([
        'jalan' => $request->input('jalan'),
        'kelurahan' => $request->input('kelurahan'),
        'kecamatan' => $request->input('kecamatan'),
        'kota' => $request->input('kota'),
        'kode_pos' => $request->input('kode_pos'),
    ]);

    return response()->json(['message' => 'Successfully updated Address'], 200);
}
    public function delete($id)
    {
        $address = Address::where('id', $id)->first();
        if (!$address) {
            return response()->json(['success' => false, "Message" => "Address Gagal Dihapus"], 404);
        }
        $address->delete();
        return response()->json(['success' => true, "Message" => "Address Berhasil Dihapus"], 200);
    }
}
