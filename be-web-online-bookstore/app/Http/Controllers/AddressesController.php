<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Services\BiteshipService;

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
            'name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:15',
            'address' => 'required|string|max:255',
            'note' => 'nullable|string|max:255',
            'postal_code' => 'required|numeric',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $data = $request->only([
            'name',
            'contact_name',
            'contact_phone',
            'address',
            'note',
            'postal_code',
            'latitude',
            'longitude'
        ]);

        $response = $this->biteshipService->addAddress($data);

        return response()->json($response);
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
