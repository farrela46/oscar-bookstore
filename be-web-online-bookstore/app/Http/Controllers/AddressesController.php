<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Services\BiteshipService;
use Illuminate\Support\Facades\Auth;

class AddressesController extends Controller
{
    protected $biteshipService;

    public function __construct(BiteshipService $biteshipService)
    {
        $this->biteshipService = $biteshipService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'selected_address_id' => 'required|string',
            'name' => 'required|string',
            'penerima' => 'required|string',
            'no_penerima' => 'required|string',
            'provinsi' => 'required|string',
            'kota' => 'required|string',
            'kecamatan' => 'required|string',
            'label' => 'nullable|string',
        ]);

        $address = new Address();
        $address->user_id = Auth::id();
        $address->selected_address_id = $request->selected_address_id;
        $address->name = $request->name;
        $address->penerima = $request->penerima;
        $address->no_penerima = $request->no_penerima;
        $address->provinsi = $request->provinsi;
        $address->kota = $request->kota;
        $address->kecamatan = $request->kecamatan;
        $address->postal_code = $request->postal_code;
        $address->alamat_lengkap = $request->alamat_lengkap;
        $address->label = $request->label;
        $address->save();

        return response()->json(['message' => 'Address saved successfully']);
    }

    public function getUserAddresses()
    {
        $user = Auth::user();
        $addresses = Address::where('user_id', $user->id)->get();

        return response()->json(['addresses' => $addresses], 200);
    }
    public function getAreas(Request $request)
    {
        $input = $request->query('input', '');

        try {
            $areas = $this->biteshipService->getAreas($input);
            return response()->json($areas);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
